#!/bin/bash

df -k  | awk '{print $1","$5}' | grep -v Filesystem
