#!/bin/sh

nohup php ./run.php scrape danjipai db > ~/game-spider.log 2>&1 &
