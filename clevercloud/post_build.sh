# Database migrations
./bin/console doctrine:migrations:migrate -n
# Frontend build
yarn install --non-interactive --pure-lockfile --force --production=false && yarn run build
