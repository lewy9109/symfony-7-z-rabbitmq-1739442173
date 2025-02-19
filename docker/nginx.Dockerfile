FROM nginx:1.19

COPY ./docker/etc/nginx /nginx

# Configure nginx
RUN rm -rf /etc/nginx/nginx.conf /etc/nginx/conf.d && \
    mkdir -p /etc/nginx/conf.d && \
    cp -f /nginx/nginx.conf /etc/nginx/nginx.conf && \
    cp -f /nginx/web.conf /etc/nginx/conf.d/web.conf

COPY ./app /var/www/html
