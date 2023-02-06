FROM debian:bullseye

RUN apt update && apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2 curl
RUN echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | tee /etc/apt/sources.list.d/sury-php.list
RUN curl -fsSL  https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/trusted.gpg.d/sury-keyring.gpg
RUN apt update \
&& apt install -y php8.1 php8.1-curl php8.1-mysql php8.1-sqlite3 php8.1-zip php8.1-mbstring php8.1-bz2 zip

RUN git clone
