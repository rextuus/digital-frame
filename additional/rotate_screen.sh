#!/bin/bash
export XDG_RUNTIME_DIR=/run/user/1000
export DISPLAY=:0
wlr-randr --output HDMI-A-2 --mode 1920x1080
wlr-randr --output HDMI-A-2 --transform 90
