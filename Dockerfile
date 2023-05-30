FROM php:7.2-cli

# To install composer with php
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;";
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /bin/composer
RUN chmod +x /bin/composer

# 国内镜像
RUN echo '' > /etc/apt/sources.list; \
    echo 'deb https://mirrors.aliyun.com/debian/ bullseye main non-free contrib' >> /etc/apt/sources.list; \
    echo 'deb-src https://mirrors.aliyun.com/debian/ bullseye main non-free contrib' >> /etc/apt/sources.list; \
    echo 'deb https://mirrors.aliyun.com/debian-security/ bullseye-security main' >> /etc/apt/sources.list; \
    echo 'deb-src https://mirrors.aliyun.com/debian-security/ bullseye-security main' >> /etc/apt/sources.list; \
    echo 'deb https://mirrors.aliyun.com/debian/ bullseye-updates main non-free contrib' >> /etc/apt/sources.list; \
    echo 'deb-src https://mirrors.aliyun.com/debian/ bullseye-updates main non-free contrib' >> /etc/apt/sources.list; \
    echo 'deb https://mirrors.aliyun.com/debian/ bullseye-backports main non-free contrib' >> /etc/apt/sources.list; \
    echo 'deb-src https://mirrors.aliyun.com/debian/ bullseye-backports main non-free contrib' >> /etc/apt/sources.list;

# To install gd extension.
RUN apt-get update && apt-get install -y \
		libfreetype6-dev \
		libjpeg62-turbo-dev \
		libpng-dev \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
	&& docker-php-ext-install -j$(nproc) gd

# To install zip extension
RUN apt-get install -y libzip-dev;\
    docker-php-ext-configure zip --with-libzip; \
    docker-php-ext-install zip;


