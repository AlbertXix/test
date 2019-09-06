#!/bin/bash

if [ $1 == "gen" ]; then
    ./gen_redis_data.php 1250 1250 2813
elif [ $1 == "test" ]; then
    ./test_nsm_1804_api.php 1251 2126
else
    ./gen_redis_data.php 1250 1250 2813
    ./test_nsm_1804_api.php 1251 2126
fi
