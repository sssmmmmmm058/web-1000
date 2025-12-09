#!/bin/bash

RANDOM_STR=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 8 | head -n 1)

FOLDER_NAME="${RANDOM_STR}flag"

FLAG_CONTENT=${GZCTF_FLAG:-"请在启动容器时通过-e GZCTF_FLAG注入真实flag"}

TMP_DIR="/tmp/ctf_temp"
mkdir -p ${TMP_DIR}/${FOLDER_NAME}

echo "${FLAG_CONTENT}" > ${TMP_DIR}/${FOLDER_NAME}/flag.txt

cd ${TMP_DIR} && zip -r /usr/share/nginx/html/www.zip ${FOLDER_NAME}

rm -rf ${TMP_DIR}

echo "压缩包生成完成！"
echo "文件夹名称：${FOLDER_NAME}"
echo "flag内容：${FLAG_CONTENT}"