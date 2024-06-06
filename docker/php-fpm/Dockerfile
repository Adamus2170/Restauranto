FROM php:8.2-fpm-alpine

COPY wait-for-it.sh /usr/bin/wait-for-it

RUN apk --update --no-cache add git libxml2-dev imap-dev openssl-dev ldb-dev libldap openldap-dev libpng-dev bash dos2unix libzip-dev zip

RUN dos2unix /usr/bin/wait-for-it

RUN chmod +x /usr/bin/wait-for-it

RUN docker-php-ext-install pdo_mysql soap gd opcache zip

RUN docker-php-ext-configure imap --with-imap --with-imap-ssl \
    && docker-php-ext-install imap

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

#CRON:
# RUN echo '*  *  *  *  *    php /var/www/bin/console app:ticket:bok-inbox:load > /dev/null 2>&1' > /etc/crontabs/root
# RUN echo '*  *  *  *  *    php /var/www/bin/console app:ticket:bok-s:load > /dev/null 2>&1' >> /etc/crontabs/root
# RUN echo '*  *  *  *  *    php /var/www/bin/console app:spool:send > /dev/null 2>&1' >> /etc/crontabs/root

# LDAP:
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions ldap

# Files permissions
RUN apk --no-cache add shadow && usermod -u 1000 www-data

CMD chmod -R 777 /var/www/var ; \
    wait-for-it database:3306 -- composer install ; \
    php bin/console cache:warmup --env=prod ; \
    crond -l 8 ; \
    php-fpm

EXPOSE 9000
