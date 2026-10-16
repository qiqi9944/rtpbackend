#!/bin/bash
# 洛图科技 观研-活动「分组」功能部署脚本（Docker / 宝塔 通用）
# 用途：一键把「活动分组」相关代码从 GitHub 部署到线上服务器并建表、重启 PHP。
#       （分组能力 = ActivityGroupController + 两个 View + NewsController/News_add/News_index 改造
#         + WxapiController(getnews type=1 按分组返回) + Index_index 菜单 + lt_activity_group 表
#         + lt_news.group_id 字段）
#
# 用法（在服务器上以 root 运行）：
#   bash deploy-activity-group.sh [应用根目录]
# 默认根目录：/root/luotu/legacy/app  （Docker 服务器）
# 宝塔环境：  bash deploy-activity-group.sh /www/wwwroot/rtpbackend
#
# 若服务器无法直连 GitHub，可把本脚本同目录下的文件手动上传后改用：
#   bash deploy-activity-group.sh --local /本地目录/ 线上根目录

set -e

BASE="https://raw.githubusercontent.com/qiqi9944/rtpbackend/main/legacy-docker/app"
SRC_REPO="https://raw.githubusercontent.com/qiqi9944/rtpbackend/main/legacy-docker"
ROOT="${1:-/root/luotu/legacy/app}"
TMP="$(mktemp -d /tmp/activitygroup_deploy.XXXXXX)"

FILES=(
  "Application/System/Controller/WxapiController.class.php|WxapiController.class.php|function getnews"
  "Application/System/Controller/NewsController.class.php|NewsController.class.php|class NewsController"
  "Application/System/Controller/ActivityGroupController.class.php|ActivityGroupController.class.php|class ActivityGroupController"
  "Application/System/View/News_index.html|News_index.html|新闻管理"
  "Application/System/View/News_add.html|News_add.html|上传图片"
  "Application/System/View/ActivityGroup_index.html|ActivityGroup_index.html|活动分组"
  "Application/System/View/ActivityGroup_add.html|ActivityGroup_add.html|分组名称"
  "Application/System/View/Index_index.html|Index_index.html|活动分组"
)

if [ "$1" = "--local" ]; then
    LOCAL="$2"
    ROOT="$3"
    [ -z "$LOCAL" ] || [ -z "$ROOT" ] && { echo "用法: deploy-activity-group.sh --local <本地目录> <线上根目录>"; exit 1; }
    MODE="local"
    echo "[*] 本地模式：从 $LOCAL 读取文件，部署到 $ROOT"
else
    MODE="remote"
    echo "[*] 从 GitHub 拉取最新代码，部署到 $ROOT"
fi

# 0) 目录存在性
[ -d "$ROOT/Application/System/Controller" ] || { echo "[!] 未找到 $ROOT/Application/System/Controller，请确认线上根目录"; exit 1; }
[ -d "$ROOT/Application/System/View" ] || { echo "[!] 未找到 $ROOT/Application/System/View"; exit 1; }

# 1) 逐个下载并校验、备份、替换
for entry in "${FILES[@]}"; do
    rel="${entry%%|*}"; rest="${entry#*|}"
    local_name="${rest%%|*}"; marker="${rest#*|}"
    DEST="$ROOT/$rel"
    if [ "$MODE" = "local" ]; then
        SRC="$LOCAL/$local_name"
        [ -f "$SRC" ] || { echo "[!] 本地缺少 $local_name，已中止"; rm -rf "$TMP"; exit 1; }
        cp "$SRC" "$TMP/$local_name"
    else
        echo "[*] 下载 $rel ..."
        curl -fsSL -m 60 "$BASE/$rel" -o "$TMP/$local_name" || { echo "[!] 下载失败: $rel"; rm -rf "$TMP"; exit 1; }
    fi
    grep -q "$marker" "$TMP/$local_name" || { echo "[!] 校验失败：$local_name 中未找到 '$marker'，已中止"; rm -rf "$TMP"; exit 1; }
    if [ -f "$DEST" ]; then
        cp "$DEST" "$DEST.bak.$(date +%Y%m%d%H%M%S)"
    fi
    cp "$TMP/$local_name" "$DEST"
    echo "    ✓ $rel"
done

# 2) 建表 lt_activity_group + lt_news.group_id 字段
echo "[*] 准备创建 lt_activity_group 数据表 / 升级 lt_news 活动分组字段 ..."
CONF="$ROOT/Application/Common/Conf/config.php"
DB_HOST=""; DB_NAME=""; DB_USER=""; DB_PWD=""
if [ -f "$CONF" ]; then
    DB_HOST=$(grep -oP "'DB_HOST'\s*=>\s*'\K[^']+" "$CONF" | head -1)
    DB_NAME=$(grep -oP "'DB_NAME'\s*=>\s*'\K[^']+" "$CONF" | head -1)
    DB_USER=$(grep -oP "'DB_USER'\s*=>\s*'\K[^']+" "$CONF" | head -1)
    DB_PWD=$(grep -oP "'DB_PWD'\s*=>\s*'\K[^']+" "$CONF" | head -1)
fi
if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo "[!] 无法从 $CONF 读取数据库配置，跳过建表。"
    echo "    请手动执行 $SRC_REPO/mysql/migrations/20261016_add_activity_group.sql"
else
    if [ "$MODE" = "local" ]; then
        SQL="$LOCAL/20261016_add_activity_group.sql"
    else
        SQL="$TMP/20261016_add_activity_group.sql"
        curl -fsSL -m 60 "$SRC_REPO/mysql/migrations/20261016_add_activity_group.sql" -o "$SQL" || { echo "[!] 下载 SQL 失败"; rm -rf "$TMP"; exit 1; }
    fi
    grep -q "lt_activity_group" "$SQL" || { echo "[!] SQL 校验失败"; rm -rf "$TMP"; exit 1; }

    MYSQL_CONTAINER=$(docker ps --format '{{.Names}}' 2>/dev/null | grep -E 'luotu-legacy-mysql|legacy-mysql' | head -1)
    run_sql() {
        if [ -n "$MYSQL_CONTAINER" ]; then
            docker exec -i "$MYSQL_CONTAINER" mysql -h127.0.0.1 -u"$DB_USER" -p"$DB_PWD" "$DB_NAME" < "$SQL"
        else
            MYSQL_HOST="${DB_HOST:-localhost}"
            [ "$MYSQL_HOST" = "legacy-mysql" ] && MYSQL_HOST="localhost"
            mysql -h"$MYSQL_HOST" -u"$DB_USER" -p"$DB_PWD" "$DB_NAME" < "$SQL"
        fi
    }
    if [ -n "$MYSQL_CONTAINER" ] || command -v mysql >/dev/null 2>&1; then
        # group_id 字段已存在时 ALTER 会报 Duplicate column，属正常，忽略
        if run_sql 2>/dev/null; then
            echo "    ✓ lt_activity_group 表已创建、lt_news.group_id 已就绪"
        else
            echo "    (group_id 字段可能已存在；如未建表请手动执行 20261016_add_activity_group.sql)"
        fi
    else
        echo "[!] 服务器无 mysql 命令也未检测到 Docker，请手动执行 20261016_add_activity_group.sql"
    fi
fi

rm -rf "$TMP"

# 3) 重启 PHP（优先 Docker，其次宝塔/系统服务）
echo "[*] 重启 PHP ..."
PHP_CONTAINER=$(docker ps --format '{{.Names}}' 2>/dev/null | grep -E 'luotu-legacy-php-fpm|legacy-php-fpm' | head -1)
if [ -n "$PHP_CONTAINER" ]; then
    docker restart "$PHP_CONTAINER" 2>/dev/null && echo "    已重启容器 $PHP_CONTAINER"
elif command -v php >/dev/null 2>&1; then
    for svc in php-fpm php7.2-fpm php-fpm-7.2 php8.0-fpm php8.1-fpm php8.2-fpm; do
        if systemctl list-unit-files 2>/dev/null | grep -q "$svc"; then
            systemctl restart "$svc" 2>/dev/null && { echo "    已重启 $svc"; break; }
        fi
    done
    php -r 'if(function_exists("opcache_reset")){opcache_reset();}' 2>/dev/null || true
fi
if command -v bt >/dev/null 2>&1; then
    bt reload 2>/dev/null || true
fi

echo "[✓] 部署完成。"
echo "    验证：登录后台应看到「活动分组」菜单；"
echo "          curl 'https://api.runtotech.com/Wxapi/getnews?type=1' 应返回 status=1 且含 groups（行业会议/秒懂趋势/权威发布）"
