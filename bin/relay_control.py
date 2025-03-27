import sys
import RPi.GPIO as GPIO
import subprocess
import time

# GPIO Pin number
RELAY_PIN = 4

# Use BCM numbering
GPIO.setmode(GPIO.BCM)
GPIO.setup(RELAY_PIN, GPIO.OUT)

if len(sys.argv) != 2:
    print("Usage: python3 relay_control.py <on|off>")
    sys.exit(1)

command = sys.argv[1]

if command == "on":
    GPIO.output(RELAY_PIN, GPIO.LOW)
    print("Relay is ON - GPIO LOW")

    # Delay to allow the screen to fully start
    time.sleep(5)  # Wait for 5 seconds (adjust as necessary)

    # Execute the rotate-screen.sh script
    try:
        subprocess.run(["/home/master/rotate-screen.sh"], check=True)
        print("rotate-screen.sh executed successfully")
    except subprocess.CalledProcessError as e:
        print(f"Error executing rotate-screen.sh: {e}")
elif command == "off":
    GPIO.output(RELAY_PIN, GPIO.HIGH)
    print("Relay is OFF - GPIO HIGH")
else:
    print("Invalid command")
    sys.exit(1)