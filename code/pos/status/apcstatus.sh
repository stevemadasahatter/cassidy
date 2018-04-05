#!/bin/bash

ps -ef | grep apache2 | grep -v grep | wc -l
