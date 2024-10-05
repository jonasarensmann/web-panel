apt-get -y install build-essential cmake git libjson-c-dev libwebsockets-dev
git clone https://github.com/tsl0922/ttyd.git
cd ttyd && mkdir build && cd build
cmake ..
make && make install

# Create a systemd service for ttyd
cat <<EOF > /etc/systemd/system/ttyd.service
[Unit]
Description=Terminal over HTTP

[Service]
User=root
ExecStart=/usr/local/bin/ttyd -p 7681 -W login
Restart=on-abort

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload
systemctl enable --now ttyd