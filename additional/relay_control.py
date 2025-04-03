import sys
import RPi.GPIO as GPIO

# GPIO Pin number
RELAY_PIN = 4

# Use BCM numbering
GPIO.setmode(GPIO.BCM)
GPIO.setup(RELAY_PIN, GPIO.OUT)

if len(sys.argv) != 2:
    print("Usage: python3 relay_control.py <on|off|status>")
    sys.exit(1)

command = sys.argv[1]

if command == "on":
    GPIO.output(RELAY_PIN, GPIO.LOW)
    print("Relay is ON - GPIO LOW")
elif command == "off":
    GPIO.output(RELAY_PIN, GPIO.HIGH)
    print("Relay is OFF - GPIO HIGH")
else:
    print("Invalid command")
    sys.exit(1)
