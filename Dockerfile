FROM debian:bullseye

WORKDIR /tmp

RUN apt update && apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2 curl git
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list
RUN curl -fsSL  https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg
RUN apt update \
&& apt install -y php8.1 php8.1-xml php8.1-curl php8.1-mysql php8.1-sqlite3 php8.1-zip php8.1-mbstring php8.1-bz2 zip

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/bin --filename=composer

RUN mkdir -p /app
RUN git clone https://github.com/mooseh/enphase-prometheus.git /app
RUN cp .env.example .env


WORKDIR /app/src
RUN composer install

ENTRYPOINT php artisan serve --host 0.0.0.0

