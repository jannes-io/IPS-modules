# Corps Of Engineers IPS modules.
This monorepo contains several IPS applications as well as a useful script to run 3rd party applications in "IN_DEV" mode.

# License
All code in this repository is currently proprietary property of [jannes-io](https://jannes.io/) until further notice. Any use (this includes usage, sharing, downloading, reselling and modifying) without explicit written consent from [jannes-io](https://jannes.io/) is subject to legal action.

## Instructions
### Prerequisites
- A LAMP server, preferably in docker, a docker-compose file is present in this repo.

#### Using docker
If you chose to go with a LAMP stack, you're on your own. This documentation is only for those who chose to use docker.
1. Start docker engine
2. Run `docker-compose up -d` in your terminal/cmd/powershell
3. Navigate to http://localhost and enjoy developing!

If you need to interact with PERSCOM templates run this once:
1. Run `docker exec -it thirdinf_ipboard bash` in your terminal/cmd/powershell
2. Run `cd /var/www/html` in your terminal/cmd/powershell
3. Run `php unbuildApplication.php perscom` in your terminal/cmd/powershell

## AWS Server Manager
The AWS Server Manager is an application that interacts with AWS EC2 instances to turn them on/off and provide automatic cleanup as well as steam query info.

### Usage
#### Settings
In ACP you can find all global application settings. AWS IAM credentials with programmatic access and EC2 permissions is required.

#### Servers
In ACP, under "Servers", you can configure different EC2 Instances, or "Servers". Depeding on the game, you need to supply the connection info, a name, as well as the steam query port.
Upon saving you can configure which groups can start, stop or reboot a server.

Once configured, nagivate to http://mysite/servers, here you will find different options to start/stop/reboot servers as well as how many players there are and so on from steam.

#### Auto-shutdown
To save on EC2 credits or cost you can configure auto-shutdown. A backend task named "stopawsservers" runs every 5 minutes and scans all servers for steam info. Once the server has been empty by a configurable amount of minutes (under settings), it will automatically shut down the server.

## Thirdinf
This module is deprecated by Perscom Enhanced and should not be developed anymore.

## Perscom Enhanced
The newest, latest and greatest application for PERSCOM users. PENH aims to add common functionality to PERSCOM that is found in many units.

This documentation is a WIP, add an entry for each module.

### Usage
#### Operations
Operations are collections of missions that follow a similar time-line. Operations are the base off event hierarchy.
Once an operation is created, missions can be made. Missions have the option to automatically create calendar events and combat record entries.

Finally, after the event, the combat unit supervisor can create an AAR which would include attendance for their combat unit. This will trigger the combat record assignment as well as populate the attendance sheet.

##### Settings
All settings regarding the operations module can be found in ACP under "Operations -> Settings".
Operations can be managed in ACP under "Operations -> Operations".

#### Personnel
Personnel currently consists of tracking and statistic tools such as the Attendance Sheet and the Strength Sheet.

##### Attendance Sheet
The attendance sheet is populated by after action reports. Multiple combat units can be selected when generating the sheet over any period.

##### Strength Sheet
Strength sheet lists a combat unit's child units and their numbers. This is useful when assigning new members to combat units and allows them to be filled equally. Additionally, this tool has some total statistics for tracking purposes. 

#### Notable medals/awards
Shows a small widget on a soldier's P-File with notable medals or awards that they have earned during their career.

##### Settings
Configurable in ACP under "Settings -> Settings -> P-File Configuration", enable awards in "Awards to highlight".

##### Known issues
Due to limitations in PERSCOM, and there being no real link between a soldier and their awards, the system currently runs off of service records. When an award has been renamed, service records aren't updated and thus notable medals will disappear. To fix this, rename the service record of everyone that has that medal.
