#!/bin/bash

# check if root
if [ "$EUID" -ne 0 ]
  then echo "Install script must be run as root"
  exit
fi

# check if /root/web-panel exists
if [ ! -d "$HOME/web-panel" ]; then
  echo "web-panel directory not found. Please clone the repository to /root/web-panel"
  exit
fi

# update
apt-get update 

# deps
if [ -x "$(command -v wget)" ]; then
  echo "wget is already installed"
else
  apt-get install -y wget
fi

if [ -x "$(command -v sqlite3)" ]; then
  echo "sqlite3 is already installed"
else
  apt-get install -y sqlite3
fi

if [ -x "$(command -v curl)" ]; then
  echo "curl is already installed"
else
  apt-get install -y curl
fi

# install go
if [ -x "$(command -v go)" ]; then
  echo "Go is already installed"
else
  if [ $(uname -m) == "armv7l" ]; then
    wget https://golang.org/dl/go1.22.5.linux-armv6l.tar.gz
    rm -rf /usr/local/go && tar -C /usr/local -xzf go1.22.5.linux-armv6l.tar.gz 
    rm -rf go1.22.5.linux-armv6l.tar.gz 
  elif [ $(uname -m) == "aarch64" ]; then
    wget https://golang.org/dl/go1.22.5.linux-arm64.tar.gz
    rm -rf /usr/local/go && tar -C /usr/local -xzf go1.22.5.linux-arm64.tar.gz 
    rm -rf go1.22.5.linux-arm64.tar.gz 
  else 
    wget https://go.dev/dl/go1.22.5.linux-amd64.tar.gz
    rm -rf /usr/local/go && tar -C /usr/local -xzf go1.22.5.linux-amd64.tar.gz 
    rm -rf go1.22.5.linux-amd64.tar.gz 
  fi
fi

echo "export PATH=$PATH:/usr/local/go/bin" >> /etc/profile 
. /etc/profile 

# serve installer
cd $HOME/web-panel/installer
go mod tidy 
go run main.go $1 $2 $3