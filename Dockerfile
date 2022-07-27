FROM phpswoole/swoole:php8.1

RUN printf "deb https://mirrors.tuna.tsinghua.edu.cn/debian/ bullseye main contrib non-free\ndeb https://mirrors.tuna.tsinghua.edu.cn/debian/ bullseye-updates main contrib non-free\ndeb https://mirrors.tuna.tsinghua.edu.cn/debian/ bullseye-backports main contrib non-free\ndeb https://mirrors.tuna.tsinghua.edu.cn/debian-security bullseye-security main contrib non-free" > /etc/apt/sources.list \
  && apt update && apt install -y cron geoipupdate \
  && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN pecl install redis  \
    && docker-php-ext-enable redis \
    && rm -rf  /tmp/* /var/tmp/*

COPY ./ /var/www
COPY ./docker/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
