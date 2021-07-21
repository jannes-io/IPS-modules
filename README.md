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

This documentation is a WIP
