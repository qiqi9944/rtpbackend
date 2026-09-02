#!/bin/bash
# 洛图科技 观研-小视频 后台功能部署脚本
# 用途：一键把「视频管理」相关代码从 GitHub 部署到线上服务器并建表、重启 PHP。
#       （视频管理 = VideoController + 两个 View + Index_index 菜单 + WxapiController(getvideo)
#         + lt_video 数据表）
#
# 用法（在服务器上以 root 运行）：
#   bash deploy-video.sh [应用根目录]
# 默认根目录：/www/wwwroot/rtpbackend
# 若路径不同，用第一个参数传入，例如：
#   bash deploy-video.sh /var/www/html
#
# 若服务器无法直连 GitHub，可把本脚本同目录下的文件手动上传后改用：
#   bash deploy-video.sh --local /本地目录/ 线上根目录
#   （--local 模式从本地目录读取下面列出的 5 个文件 + lt_video.sql）

set -e

BASE="https://raw.githubusercontent.com/qiqi9944/rtpbackend/main/legacy-docker/app"
ROOT="${1:-/www/wwwroot/rtpbackend}"
SRC_REPO="https://raw.githubusercontent.com/qiqi9944/rtpbackend/main/legacy-docker"
TMP="$(mktemp -d /tmp/video_deploy.XXXXXX)"

FILES=(
  "Application/System/Controller/WxapiController.class.php|WxapiController.class.php|function getvideo"
  "Application/System/Controller/VideoController.class.php|VideoController.class.php|class VideoController"
  "Application/System/View/Video_index.html|Video_index.html|视频管理"
  "Application/System/View/Video_add.html|Video_add.html|上传图片"
  "Application/System/View/Index_index.html|Index_index.html|视频管理"
)

if [ "$1" = "--local" ]; then
    LOCAL="$2"
    ROOT="$3"
    [ -z "$LOCAL" ] || [ -z "$ROOT" ] && { echo "用法: deploy-video.sh --local <本地目录> <线上根目录>"; exit 1; }
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

# 2) 建表 lt_video（使用应用自身数据库账号，从线上 config.php 读取）
echo "[*] 准备创建 lt_video 数据表 ..."
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
    echo "    请手动执行 lt_video.sql（文件：$SRC_REPO/mysql/init/lt_video.sql）"
else
    if [ "$MODE" = "local" ]; then
        SQL="$LOCAL/lt_video.sql"
    else
        SQL="$TMP/lt_video.sql"
        curl -fsSL -m 60 "$SRC_REPO/mysql/init/lt_video.sql" -o "$SQL" || { echo "[!] 下载 lt_video.sql 失败"; rm -rf "$TMP"; exit 1; }
    fi
    grep -q "CREATE TABLE" "$SQL" || { echo "[!] lt_video.sql 校验失败"; rm -rf "$TMP"; exit 1; }
    if command -v mysql >/dev/null 2>&1; then
        MYSQL_HOST="${DB_HOST:-localhost}"
        [ "$MYSQL_HOST" = "localhost" ] || [ "$MYSQL_HOST" = "127.0.0.1" ] || MYSQL_HOST="localhost"
        if mysql -h"$MYSQL_HOST" -u"$DB_USER" -p"$DB_PWD" "$DB_NAME" < "$SQL" 2>/dev/null; then
            echo "    ✓ lt_video 表已创建（数据库 $DB_NAME）"
        else
            echo "[!] mysql 执行建表失败，请手动在 phpMyAdmin 中执行 lt_video.sql"
        fi
    else
        echo "[!] 服务器无 mysql 命令，请手动在 phpMyAdmin 中执行 lt_video.sql"
    fi
fi

rm -rf "$TMP"

# 3) 重启 PHP-FPM（常见发行版逐一尝试）
echo "[*] 重启 PHP-FPM ..."
for svc in php-fpm php7.2-fpm php-fpm-7.2 php8.0-fpm php8.1-fpm php8.2-fpm; do
    if systemctl list-unit-files 2>/dev/null | grep -q "$svc"; then
        systemctl restart "$svc" 2>/dev/null && { echo "    已重启 $svc"; break; }
    fi
done
if command -v bt >/dev/null 2>&1; then
    bt reload 2>/dev/null || true
fi
php -r 'if(function_exists("opcache_reset")){opcache_reset();}' 2>/dev/null || true

echo "[✓] 部署完成。"
echo "    验证：登录后台应看到「视频管理」菜单；curl 'https://api.runtotech.com/Wxapi/getvideo' 应返回 status=1"