# Weather Gremlin
This is a (moderately amusing) application that gives you information 
about your physical location or what the weather’s like where you are 
based on your IP address. You can also enter an IP address and get 
information about it. In addition to a simple web interface, this 
application also features an API that returns the same data in JSON 
format.

## Getting Started
This is a web application, so you need a web server and an internet 
connection. Your server should be running PHP 5 or greater.

## Installing
Weather Gremlin is built on a yii-basic template. Drop the directory 
called ‘yii-basic’ into a served directory (on my machine, this is 
C:\wamp64\www) navigate to the yii-basic directory in a console and 
enter the command ‘PHP yii serve’ and you’re off to the races. The 
application is visible at ‘localhost:8080’ on my machine, however 
this may vary depending on how your server is configured. If all’s 
gone well you should see the fancy ‘Weather Gremlin’ banner and a 
short form. 

## Web Interface
The form is pretty straightforward. Your device’s IP address 
appears as the placeholder in the IP address text box. Aside from 
that there’s radio buttons that determine what kind of information 
you want to get, where you want to get it from, and a submit 
button that actually gets it.

## API
This application returns JSON data to GET requests sent to a number 
of endpoints.

**GET/geolocation**
Location information based on your device’s IP.

**GET/weather**
Local weather information based on your device’s IP.

**GET/[geolocation|weather]/8-8-8-8**
Both of the above endpoints take an additional argument that lets 
you specify what IP they’re getting information about. In this 
example, the IP is 8.8.8.8. Note that you replace the dots with 
dashes for this request.

**GET/weather/8-8-8-8?service=[ip-api|freegeoip|default]**
The application can get location data from two different sources, 
ip-api, and freegeoip. If you want to specify which service to 
use via the API, add an additional GET parameter to the end of 
your request.

## Built With
+ Yii2
+ Yii2 Http Client
+ www.ipify.org
+ dillinger.io
+ Adobe Illustrator
