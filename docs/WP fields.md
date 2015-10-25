# Legend
/ = defined somehow (can be implemented) but not yet implemented
X/ = implemented but needs testing
X = implemented + tested
! = flag warning to RLC
? = question for RLC/WP
* = waiting to hear back

# Regex Searches

- All action items: ^-\s+(?!X\s)
- Something's ready to implement (questions may remain): ^-\s+[!?*]*/[!?*]*\s+
- Items to communicate about: ^-\s+[/X]*[!?]+[/X!?]*\s+
- Items waiting to hear back: ^-\s+[^\s]*\*[^\s]*\s+
- Items I'm blocked on: ^-\s+[^X/\s]+\s+

# Whirlpool Laundry
- X?* capacity (in cubic feet) (float) - match $1 from /(\d+(?:\.\d+))\s+cu\. ft\./ in English name, or "Washer Capacity (cu. ft.)" CF as backup
    + confirm we should include combos
    + and also that their capacity should be the washer capacity, which seems to usually be smaller. or should it be the total of the two? that seems misleading, it's not the total that matters, they serve different purposes.
- / energyStar (bool) - has CF "Energy Star\u00ae Qualified" and value != "No"
- / ecoBoost (bool) - CF "Option Selections" contains "EcoBoost"
- / quickWash (bool) - "Quick Wash" found in name or in CF "Washer Cycle Selections"
- ?* quickDry (bool) rapiddry?
    + sounds like for dryers, i thought we're not scoring those
- ?* loadAndGo (bool)
    + is it this feature? http://www.whirlpool.ca/-[YWET4027EW]-1305284/YWET4027EW/

        HE Agitator with Fabric Softener Cap
        Get high-efficiency cleaning and convenient fabric softener dispensing at just the right time so you can simply **load the washer and go**.

- / fanFresh (bool) - has CF "Fan Fresh\u00ae-Fresh Hold\u00ae" and value != "No"
- X vibrationControl (bool) - same as MTG
- / quietWash (bool) - either description or CF "Sound Package" contain "Quiet Wash"
- ?* quietDry (bool)
    + DRYERS?
- / silentSteel (bool) - has SF "SilentSteel\u2122 Dryer Drum"
- X frontLoad (bool) - same as MTG
- X topLoad (bool) - same as MTG
- / adaptiveWash (bool) - has SF "Adaptive Wash Technology"
- / colorLast (bool) - has SF "ColorLast\u2122 Option"
- / smoothWave (bool) - has SF "Smooth Wave Stainless Steel Wash Basket"
- ?* quadBaffles (bool)
    + DRYERS?
    + would be that dryer has SF "Quad Baffles"
- ?* advancedMoistureSensing (bool)
    + DRYERS?
    + has SF "Advanced Moisture Sensing System"
- ?* accuDry (bool)
    + DRYERS?
- ?* wrinkleShield (bool)
    + DRYERS?
- ?* steamRefresh (bool)
    + DRYERS?
- ?* gas (bool)
    - this is a dryer feature, right? doesn't make sense
    - unless it's for the combo washer/dryers like www.whirlpool.ca/-[WGT4027EW]-1305273/WGT4027EW/
        + confirm those should be included


# Whirlpool Dishwasher
- / targetClean (bool) - "TargetClean" in name or description
- / totalCoverageArm (bool) - has SF "TotalCoverage Spray Arm"
- / sensorCycle (bool) - has SF "Sensor Cycle"
- / ez2Lift (bool) - has SF "EZ-2-Lift\u2122 Adjustable Upper Rack"
- / silverwareSpray (bool) - has SF "Silverware Spray"
- ?* accuSense (bool)
    + same thing as sensor cycle?
    + every sensor cycle description says "Get perfect cleaning every time with the Sensor cycle. The AccuSense\u00ae soil sensor measures load size and soil level during the prewash, and the dishwasher adjusts to the right wash and dry settings throughout the wash cycle to deliver precise cleaning to your dishes."
        * e.g. http://www.whirlpool.ca/en_CA/-[WDT920SADM]-1304822/WDT920SADM/
- / placeSettings (int) - CF "Capacity" - no regex, entire value
- / compactTallTub (bool) - name contains "Compact Tall Tub"
- / decibels (int) - CF "Decibel Level"
- / anyWarePlusBasket (bool) - has SF "AnyWare\u2122 Plus Silverware Basket"
- /?* FIC (fully Integrated Console) (bool) - is in catalog group 'SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Hidden_Control_Console'
- /?* FCC (Front Control Console) (bool) - is in catalog group 'SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Visible_Front_Console'

# Whirlpool Fridge
- X width (float) - StandardAbstract processor
- X height (float) - StandardAbstract processor
- / energyStar - has CF "Energy Star\u00ae Qualified" and value != "No"
- X topMount - same as KAD
- X bottomMount - same as KAD
- / sideBySide - same as KAD (CF "Refrigerator Type" == "Side-by-Side"), but change to actually output it
- X frenchDoor - same as KAD
- ?* frenchDoor5
    + can't find, tried same method as KAD (SF "5-Door Configuration")
    + changed this to "5door" for KAD
- X filtered - same as KAD
    + renamed from "filteredWater" to be consistent
- / exteriorWater - CF "Dispenser Type" contains 'exterior' and 'water'
- / exteriorIce - CF "Dispenser Type" contains 'exterior' and 'ice'
- / factoryInstalledIce - CF "Ice Maker" contains 'factory installed'
- X counterDepth - name contains \bcounter[- ]depth\b or has SF "counter depth styling" (ignoring case)
- X standardDepth - !counterDepth
- X freshFlowProducePreserver - almost same as MTG, except for case. has SF "FreshFlow\u2122 Produce Preserver"
- / freshStor - has SF "FreshStor\u2122 Refrigerated Drawer"
- / accuChill - has SF "Accu-Chill\u2122 Temperature Management System" - I'm now just making all DescriptiveAttr searches ignore case by default. otherwise I might have missed other inconsistencies
- / accuFresh - has SF "AccuFresh\u2122 dual cooling system"
- / tripleCrisper - has SF that CONTAINS "Triple Crisper system" - sometimes "EasyView", sometimes not

## feed errors

- IC14B-NAR is not a fridge, it's a fridge accessory, but it's in the fridges category

# Whirlpool Cooktops
- width (in inches) (int)
- electric (bool)
- induction (bool)
- gas (bool)
- dishwasherSafeKnobs (bool)
- glassTouch (bool)
- accuSimmer (bool)

# Whirlpool Range
- induction (bool)
- aquaLift (bool)
- trueConvection (bool)
- accuBake (bool)
- electric (bool)
- rapidPreHeat  (bool)
- gas (bool)
- volume (int)
- maxCapacityRack (bool)
- double (bool)
- warmingDrawer (bool)
- single (bool)
- frozenBake (bool)
- rearControl (bool)
- frontControl (bool)

# Whirlpool Vents
- width (in inches) (float)
- islandMount (bool)
- wallMount (bool)
- CFM (int)
- underCabinet (bool)
- exterior (bool)
- nonVented (bool)
- convertible (bool)
- easyConversion (bool)
- microWaveHoodCombination (bool)

# Whirlpool Wall Ovens

- this was missing, confirm this category as well as others
