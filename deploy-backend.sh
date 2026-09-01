#!/bin/bash
# 洛图科技后台 getshuju 更新脚本
# 用途：把仓库里已修复的 WxapiController.class.php（含 arr_sj8 品牌市占率增减 / arr_sj5.t3 价格段增减）
#       一键替换线上文件并重启 PHP。
#
# 用法（在服务器上以 root 运行）：
#   bash deploy-backend.sh [线上文件路径]
# 默认路径：/www/wwwroot/rtpbackend/Application/System/Controller/WxapiController.class.php
# 若线上路径不同，用第一个参数传入，例如：
#   bash deploy-backend.sh /var/www/html/Application/System/Controller/WxapiController.class.php
#
# 若服务器无法直连 GitHub，可把本脚本同目录下的 WxapiController.class.php 手动上传后，
# 改用： bash deploy-backend.sh --local /本地/WxapiController.class.php 线上路径

set -e

SRC_REPO="https://raw.githubusercontent.com/qiqi9944/rtpbackend/main/legacy-docker/app/Application/System/Controller/WxapiController.class.php"
SRC_FILE="${1:-/www/wwwroot/rtpbackend/Application/System/Controller/WxapiController.class.php}"
BACKUP="${SRC_FILE}.bak.$(date +%Y%m%d%H%M%S)"
TMP="$(mktemp /tmp/wxapi.XXXXXX.php)"

if [ "$1" = "--local" ]; then
    LOCAL="$2"
    DEST="$3"
    [ -z "$LOCAL" ] || [ -z "$DEST" ] && { echo "用法: deploy-backend.sh --local <本地文件> <线上路径>"; exit 1; }
    SRC_FILE="$DEST"
    cp "$LOCAL" "$TMP"
    echo "[*] 使用本地文件替换 $SRC_FILE"
else
    echo "[*] 从 GitHub 拉取最新 getshuju..."
    curl -fsSL -m 60 "$SRC_REPO" -o "$TMP" || { echo "下载失败，请检查网络或改用 --local 方式"; exit 1; }
fi

# 校验下载的文件是 PHP 且含关键函数
grep -q "function getshuju" "$TMP" || { echo "文件校验失败：未找到 getshuju，已中止"; rm -f "$TMP"; exit 1; }
grep -q "arr_sj8" "$TMP" || echo "[!] 警告：文件中未找到 arr_sj8，可能不是最新版"

echo "[*] 备份原文件 -> $BACKUP"
cp "$SRC_FILE" "$BACKUP"

echo "[*] 替换文件"
cat "$TMP" > "$SRC_FILE"
rm -f "$TMP"

echo "[*] 校验 PHP 语法..."
if command -v php >/dev/null 2>&1; then
    php -l "$SRC_FILE"
fi

echo "[*] 重启 PHP-FPM（常见发行版逐一尝试）"
for svc in php-fpm php7.2-fpm php-fpm-7.2; do
    if systemctl list-unit-files 2>/dev/null | grep -q "$svc"; then
        systemctl restart "$svc" 2>/dev/null && { echo "已重启 $svc"; break; }
    fi
done
# 宝塔面板常见命令
if command -v bt >/dev/null 2>&1; then
    bt reload 2>/dev/null || true
fi
# 兜底：让 opcache 失效（下个请求会重新编译 PHP 文件）
php -r 'if(function_exists("opcache_reset")){opcache_reset();}' 2>/dev/null || true

echo "[✓] 部署完成。原文件已备份到 $BACKUP"
echo "    验证：curl 'https://api.runtotech.com/Wxapi/getshuju?type=1&sel_xl=1&sel_yd=1&uid=3' 应能看到 arr_sj8"