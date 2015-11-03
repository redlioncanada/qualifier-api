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
        * NO --> see if it's easy to exclude them
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
- / FIC (fully Integrated Console) (bool) - is in catalog group 'SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Hidden_Control_Console'
- / FCC (Front Control Console) (bool) - is in catalog group 'SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Visible_Front_Console'

# Whirlpool Fridge

- X width (float) - StandardAbstract processor
- X height (float) - StandardAbstract processor
- / energyStar - has CF "Energy Star\u00ae Qualified" and value != "No"
- X topMount - same as KAD
- X bottomMount - same as KAD
- / sideBySide - same as KAD (CF "Refrigerator Type" == "Side-by-Side"), but change to actually output it
- X frenchDoor - same as KAD
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

## fields not implemented for now

- frenchDoor5
    + can't find, tried same method as KAD (SF "5-Door Configuration")
    + changed this to "5door" for KAD
    + Chris flagged that data isn't avail
    + https://trello.com/c/tN1saGz5/6-french-door-4-5-door

## feed errors

- IC14B-NAR is not a fridge, it's a fridge accessory, but it's in the fridges category

# Whirlpool Cooktops

- X width (in inches) (int)
- X electric (bool)
- X induction (bool)
- X gas (bool)
- / dishwasherSafeKnobs (bool) - has SF "Dishwasher-Safe Knobs"
- / glassTouch (bool) - has SF "Glass Touch Controls"
- / accuSimmer (bool) - description contains "AccuSimmer" or has SF containing "AccuSimmer"

# Whirlpool Range

- !* induction (bool) - none matching
- / aquaLift (bool) - name or description contain "aqualift" or has SF "AquaLift\u00ae Self-Clean technology"
- / trueConvection (bool) - name or description or a SF contains "true convection"
- / accuBake (bool) - has SF "AccuBake\u00ae Temperature Management System"
- X electric (bool) - same as KAD
- / rapidPreHeat  (bool) - has SF "Rapid Preheat"
- X gas (bool) - same as KAD
- X capacity (int)
- / maxCapacityRack (bool) - CF "Oven Rack Type" contains "max capacity" *OR* has SF "Max Capacity Recessed Rack"
    + implemented the latter already (copied from MTG)
- X double (bool) - same as KAD
- X warmingDrawer (bool) - same as KAD
- / single (bool) - should just be !double, but KAD didn't have
- / frozenBake (bool) - has SF "Frozen Bake\u2122 Technology"
- X?* rearControl (bool) - same as MTG (recent update to MTG)
- X?* frontControl (bool) - same as MTG (recent update to MTG)
    + they're both false for YWGE755C0BS-NAR
    + i probably just missed it because the string is different even though it contains freestanding

# Whirlpool Hoods

- X width (in inches) (float)
- X islandMount (bool) - always false
- X wallMount (bool)- always false
- X underCabinet (bool) - but may interact with islandMount and wallMount
- / CFM (int) - null for UXW7324BSS-NAR
    + also try "(\d+)[\s-]CFM" in description, but prefer CF if it exists
- X exterior (bool)
- X nonVented (bool)
- X convertible (bool)
- ?* easyConversion (bool)
    + can't find
- ?* microWaveHoodCombination (bool)
    + these are a separate category -- listed under SC_Kitchen_Cooking_Microwaves_Over_The_Range category
    + see http://www.whirlpool.com/kitchen-1/cooking-2/over-the-range-3/102110018/ and check "over the range" in the left-hand filters
    + include these in the Qualifier's "Hoods" category?

## non-field-specific questions and notes

- WP calls this category "Hoods", not "Vents", so the API does too
- not hoods, just blowers -- like KAD issue - error?
    + they all have CF hood type = In-Line Blower
    + UXB0600DYS-NAR
    + UXB1200DYS-NAR
    + UXI1200DYS-NAR
    + UXB1200DYS-NAR
    + UXB0600DYS-NAR

# Whirlpool Wall Ovens

- X width
- X capacity
    + no capacity info for 1 of 8: http://www.whirlpool.ca/-[WOS52EM4AS]-1304404/WOS52EM4AS/
- X single
- X double
- X combination
- X trueConvection
- / accuBake (bool) - has SF "AccuBake\u00ae Temperature Management System" (same as Ranges)
- / digitalThermometer - has SF containing "thermometer"
