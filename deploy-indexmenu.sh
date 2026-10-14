#!/bin/bash
# 洛图科技 首页九宫格（市场数据菜单）重置脚本
# 用途：把首页「市场数据」菜单及绑定品类更新为 6 个新品类组
#       （家电家居/教育办公/智能数码/运动户外/商用显示/上游供应链），
#       仅绑定后台已存在的品类（type 见 config 的 Lt_datascate2）。
#       纯数据库改动，不改 PHP、无需重启。
#
# 用法（在服务器上以 root 运行）：
#   bash deploy-indexmenu.sh [应用根目录]
# 默认根目录：/root/luotu/legacy/app      （Docker 服务器）
# 宝塔环境：  bash deploy-indexmenu.sh /www/wwwroot/rtpbackend
#
# 若服务器无法直连 GitHub，可把本脚本同目录下的 SQL 手动上传后改用：
#   bash deploy-indexmenu.sh --local /本地目录/ 线上根目录

set -e

SRC_REPO="https://raw.githubusercontent.com/qiqi9944/rtpbackend/main/legacy-docker"
ROOT="${1:-/root/luotu/legacy/app}"
TMP="$(mktemp -d /tmp/indexmenu.XXXXXX)"
MIG_NAME="20261014_reset_indexmenu_cate.sql"

if [ "$1" = "--local" ]; then
    LOCAL="$2"
    ROOT="$3"
    [ -z "$LOCAL" ] || [ -z "$ROOT" ] && { echo "用法: deploy-indexmenu.sh --local <本地目录> <线上根目录>"; exit 1; }
    MODE="local"
    echo "[*] 本地模式：从 $LOCAL 读取 SQL，部署到 $ROOT"
else
    MODE="remote"
    echo "[*] 从 GitHub 拉取 SQL，部署到 $ROOT"
fi

# 0) 校验应用目录
[ -d "$ROOT/Application/Common/Conf" ] || { echo "[!] 未找到 $ROOT/Application/Common/Conf，请确认线上根目录"; exit 1; }

# 1) 取得 SQL
SQL="$TMP/$MIG_NAME"
if [ "$MODE" = "local" ]; then
    SRC="$LOCAL/$MIG_NAME"
    [ -f "$SRC" ] || { echo "[!] 本地缺少 $MIG_NAME，已中止"; rm -rf "$TMP"; exit 1; }
    cp "$SRC" "$SQL"
else
    echo "[*] 下载 $MIG_NAME ..."
    curl -fsSL -m 60 "$SRC_REPO/mysql/migrations/$MIG_NAME" -o "$SQL" || { echo "[!] 下载失败: $MIG_NAME"; rm -rf "$TMP"; exit 1; }
fi
grep -q "lt_indexmenu" "$SQL" || { echo "[!] SQL 校验失败：未找到 lt_indexmenu，已中止"; rm -rf "$TMP"; exit 1; }

# 2) 读取数据库配置
CONF="$ROOT/Application/Common/Conf/config.php"
DB_HOST=""; DB_NAME=""; DB_USER=""; DB_PWD=""
if [ -f "$CONF" ]; then
    DB_HOST=$(grep -oP "'DB_HOST'\s*=>\s*'\K[^']+" "$CONF" | head -1)
    DB_NAME=$(grep -oP "'DB_NAME'\s*=>\s*'\K[^']+" "$CONF" | head -1)
    DB_USER=$(grep -oP "'DB_USER'\s*=>\s*'\K[^']+" "$CONF" | head -1)
    DB_PWD=$(grep -oP "'DB_PWD'\s*=>\s*'\K[^']+" "$CONF" | head -1)
fi
if [ -z "$DB_NAME" ] || [ -z "$DB_USER" ]; then
    echo "[!] 无法从 $CONF 读取数据库配置，请手动在 phpMyAdmin 中执行 $MIG_NAME"
    rm -rf "$TMP"
    exit 1
fi

# 3) 执行 SQL（优先 Docker 容器，其次本机 mysql 客户端）
run_sql() {
    if [ -n "$MYSQL_CONTAINER" ]; then
        docker exec -i "$MYSQL_CONTAINER" mysql -h127.0.0.1 -u"$DB_USER" -p"$DB_PWD" "$DB_NAME" < "$SQL"
    else
        MYSQL_HOST="${DB_HOST:-localhost}"
        [ "$MYSQL_HOST" = "legacy-mysql" ] && MYSQL_HOST="localhost"
        mysql -h"$MYSQL_HOST" -u"$DB_USER" -p"$DB_PWD" "$DB_NAME" < "$SQL"
    fi
}

MYSQL_CONTAINER=$(docker ps --format '{{.Names}}' 2>/dev/null | grep -E 'luotu-legacy-mysql|legacy-mysql' | head -1)
if [ -n "$MYSQL_CONTAINER" ] || command -v mysql >/dev/null 2>&1; then
    echo "[*] 执行菜单重置 SQL（数据库 $DB_NAME）..."
    if run_sql 2>/dev/null; then
        echo "    ✓ 首页菜单及绑定品类已更新"
    else
        echo "[!] 执行失败，请手动在 phpMyAdmin 中执行 $MIG_NAME"
        rm -rf "$TMP"
        exit 1
    fi
else
    echo "[!] 服务器无 mysql 命令也未检测到 Docker，请手动在 phpMyAdmin 中执行 $MIG_NAME"
    rm -rf "$TMP"
    exit 1
fi

rm -rf "$TMP"
echo "[✓] 部署完成（纯数据改动，无需重启 PHP）。"
echo "    验证：curl 'https://api.runtotech.com/Wxapi/getindexmenu' 应返回 6 个品类组"
echo "          （家电家居/教育办公/智能数码/运动户外/商用显示/上游供应链）"
