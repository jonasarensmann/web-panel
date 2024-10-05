#!/bin/bash

# Add User 
useradd --gid sudo \
    --create-home \
    --shell /bin/bash \
    $1 &&

# Change Password
echo "$1:$2" | chpasswd 

# Add to required groups
usermod -aG docker $1
