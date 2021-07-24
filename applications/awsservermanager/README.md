# AWS Server Manager
The AWS Server Manager is an application that interacts with AWS EC2 instances to turn them on/off and provide automatic cleanup as well as steam query info.

## Usage
#### Settings
In ACP you can find all global application settings. AWS IAM credentials with programmatic access and EC2 permissions is required.

#### Servers
In ACP, under "Servers", you can configure different EC2 Instances, or "Servers". Depeding on the game, you need to supply the connection info, a name, as well as the steam query port.
Upon saving you can configure which groups can start, stop or reboot a server.

Once configured, navigate to http://mysite/servers, here you will find different options to start/stop/reboot servers as well as how many players there are and so on from steam.

#### Auto-shutdown
To save on EC2 credits or cost you can configure auto-shutdown. A backend task named "stopawsservers" runs every 5 minutes and scans all servers for steam info. Once the server has been empty by a configurable amount of minutes (under settings), it will automatically shut down the server.
