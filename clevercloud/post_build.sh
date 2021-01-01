#!/bin/sh
# Database migrations
./bin/console doctrine:migrations:migrate -n
