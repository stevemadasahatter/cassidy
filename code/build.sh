#!/bin/bash

cd ./pos
rm -rf *
git archive prod --remote ssh://steve@stevemadasahatter.mooo.com:/home/steve/grive/code/pos.git --format tar | tar xf -
mkdir tmp
chmod -R 777 ./tmp

cd ../backend
rm -rf *
git archive prod --remote ssh://steve@stevemadasahatter.mooo.com:/home/steve/grive/code/backend.git --format tar | tar xf -
chmod -R 777 ./tmp
