# Perscom Enhanced
The newest, latest and greatest application for PERSCOM users. PENH aims to add common functionality to PERSCOM that is found in many units.

This documentation is a WIP, add an entry for each module.

## Operations
Operations are collections of missions that follow a similar time-line. Operations are the base off event hierarchy.
Once an operation is created, missions can be made. Missions have the option to automatically create calendar events and combat record entries.

Finally, after the event, the combat unit supervisor can create an AAR which would include attendance for their combat unit. 

AARs can be set up to do multiple things:
- Automatically create PERSCOM combat records.
- Send emails based on status. For example to remind a soldier of their duties when marked absent.

#### Settings
All settings regarding the Operations module can be found in ACP under "Operations -> Settings".
Operations can be managed in ACP under "Operations -> Operations".

## Personnel
Personnel currently consists of tracking and statistic tools such as the Attendance Sheet, and the Strength Sheet.

#### ORBAT
This module is a planned WIP.

#### Attendance Sheet
The attendance sheet is populated by after action reports. Multiple combat units can be selected when generating the sheet over any period.

#### Strength Sheet
Strength sheet lists a combat unit's child units and their numbers. This is useful when assigning new members to combat units and allows them to be filled equally. Additionally, this tool has some total statistics for tracking purposes. 

#### SquadXML
PERSCOM comes with a paid addon to generate SquadXMLs. The SquadXML feature in PENH is a simplified and hands-off version of that. It runs automatically every 6 hours. If you need extended SquadXML settings we recommend using the built-in PERSCOM addon. For simple, generic SquadXMLs this feature should provide the required functionality.

#### Settings
All settings regarding the Strength Sheet can be found in ACP under "Settings -> Settings -> Strength Sheet".
The attendance sheet is influenced by mission and AAR settings, which are in the Operations module.

## P-File Enhancement

#### Notable Awards
Shows a small widget on a soldier's P-File with notable medals or awards that they have earned during their career.

#### Default Uniform
Set a default uniform when there is none on soldier level.

#### Settings
Configurable in ACP under "Settings -> Settings -> P-File Configuration".

#### Known issues
Due to limitations in PERSCOM, and there being no real link between a soldier and their awards, the system currently runs off of service records. When an award has been deleted and recreated, service records aren't updated and thus those notable medals will disappear. To fix this, relink the award in the service record of everyone that had the old medal.
