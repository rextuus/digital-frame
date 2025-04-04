#!/bin/bash
# Ensure environment variables are set
export XDG_RUNTIME_DIR=/run/user/1000
export DISPLAY=:0

# Check and set ACL permissions if necessary
if [ ! -r /run/user/1000/wayland-0 ]; then
  sudo setfacl -m u:www-data:rwx /run/user/1000
  sudo setfacl -m u:www-data:rw /run/user/1000/wayland-0
fi

# Run the Wayland commands
wlr-randr --output HDMI-A-2 --mode 1920x1080
wlr-randr --output HDMI-A-2 --transform 90