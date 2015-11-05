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

## Exclude filters

- X exclude combo washer/dryers by looking for "combination" in en_CA name

## Field rules

- X capacity (in cubic feet) (float) - match $1 from /(\d+(?:\.\d+))\s+cu\. ft\./ in English name - note combos are excluded
- X energyStar (bool) - has CF "Energy Star\u00ae Qualified" and value != "No"
- X ecoBoost (bool) - CF "Option Selections" contains "EcoBoost"
- X quickWash (bool) - "Quick Wash" found in name or in CF "Washer Cycle Selections"
- X quietWash (bool) - either description or CF "Sound Package" contain "Quiet Wash"
- / loadAndGo (bool) - has SF "Load & Go™ System"
- X fanFresh (bool) - has CF "Fan Fresh\u00ae-Fresh Hold\u00ae" and value != "No"
- X vibrationControl (bool) - same as MTG
- X frontLoad (bool) - same as MTG
- X topLoad (bool) - same as MTG
- X adaptiveWash (bool) - has SF "Adaptive Wash Technology"
- X colorLast (bool) - has SF "ColorLast\u2122 Option"
- X smoothWave (bool) - has SF "Smooth Wave Stainless Steel Wash Basket"

# Whirlpool Dishwasher

- X targetClean (bool) - "TargetClean" in name or description
- X compactTallTub (bool) - name contains "Compact Tall Tub"
- X totalCoverageArm (bool) - has SF "TotalCoverage Spray Arm"
- X sensorCycle (bool) - has SF "Sensor Cycle"
- X ez2Lift (bool) - has SF "EZ-2-Lift\u2122 Adjustable Upper Rack"
- X silverwareSpray (bool) - has SF "Silverware Spray"
- X accuSense (bool) - has SF "AccuSense\u00ae Soil Sensor"
- X anyWarePlusBasket (bool) - has SF "AnyWare\u2122 Plus Silverware Basket"
- X placeSettings (int) - CF "Capacity" - entire value, but can still use regex in case it changes to include words or decimal in the future
- X decibels (int) - CF "Decibel Level"
- X FIC (fully Integrated Console) (bool) - is in catalog group 'SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Hidden_Control_Console'
- X FCC (Front Control Console) (bool) - is in catalog group 'SC_Kitchen_Dishwasher__Cleaning_Dishwashers_BuiltIn_Visible_Front_Console'

# Whirlpool Fridge

- X width (float) - StandardAbstract processor
- X height (float) - StandardAbstract processor
- X energyStar - has CF "Energy Star\u00ae Qualified" and value != "No"
- X topMount - same as KAD
- X bottomMount - same as KAD
- X sideBySide - same as KAD (CF "Refrigerator Type" == "Side-by-Side"), but change to actually output it
- X frenchDoor - same as KAD
- X filtered - same as KAD
    + renamed from "filteredWater" to be consistent
- X exteriorWater - CF "Dispenser Type" contains 'exterior' and 'water'
- X exteriorIce - CF "Dispenser Type" contains 'exterior' and 'ice'
- X factoryInstalledIce - CF "Ice Maker" contains 'factory installed'
- X counterDepth - name contains \bcounter[- ]depth\b or has SF "counter depth styling" (ignoring case)
- X standardDepth - !counterDepth
- X freshFlowProducePreserver - almost same as MTG, except for case. has SF "FreshFlow\u2122 Produce Preserver"
- X freshStor - has SF "FreshStor\u2122 Refrigerated Drawer"
- X accuChill - has SF "Accu-Chill\u2122 Temperature Management System" - I'm now just making all DescriptiveAttr searches ignore case by default. otherwise I might have missed other inconsistencies
- X accuFresh - has SF "AccuFresh\u2122 dual cooling system"
- X tripleCrisper - has SF that CONTAINS "Triple Crisper system" - sometimes "EasyView", sometimes not

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
- X dishwasherSafeKnobs (bool) - has SF "Dishwasher-Safe Knobs"
- X glassTouch (bool) - has SF "Glass Touch Controls"
- X accuSimmer (bool) - description contains "AccuSimmer" or has SF containing "AccuSimmer"

# Whirlpool Range

- X induction (bool) - name or description contain "induction"
- X aquaLift (bool) - name or description contain "aqualift" or has SF "AquaLift\u00ae Self-Clean technology"
- X trueConvection (bool) - name or description or a SF contains "true convection"
- X accuBake (bool) - has SF "AccuBake\u00ae Temperature Management System"
- X rapidPreHeat  (bool) - has SF "Rapid Preheat"
- X gas (bool) - same as KAD
- X electric (bool) - same as KAD
- X capacity (int)
- X maxCapacity (bool) - CF "Oven Rack Type" contains "max capacity" *OR* has SF "Max Capacity Recessed Rack"
    + copied the former from MTG
    + renamed from "maxCapacityRack" to match MTG
- X double (bool) - same as KAD
- X warmingDrawer (bool) - same as KAD
- X single (bool) - should just be !double, but KAD didn't have
- X frozenBake (bool) - has SF "Frozen Bake\u2122 Technology"
- X rearControl (bool) - same as MTG (recent update to MTG)
- X frontControl (bool) - same as MTG (recent update to MTG)

# Whirlpool Hoods

- X width (in inches) (float)
- X islandMount (bool) - CF "Hood Type" exists and has value "Island Canopy"
- X wallMount (bool) - CF "Hood Type" exists and has value "Wall Canopy"
- X underCabinet (bool) - CF "Hood Type" exists and has value in ["Under Cabinet", "Under-the-Cabinet"]
- X CFM (int) - same as KAD already implemented (CF), OR as backup try "(\d+)[\s-]CFM" in description, but prefer CF if it exists (added to cover UXW7324BSS-NAR)
- X exterior (bool)
- X nonVented (bool)
- X convertible (bool)
- ?* easyConversion (bool)
    + can't find
    + https://trello.com/c/jWe4kMD8/10-easyconversion
- ?* microWaveHoodCombination (bool)
    + these are a separate category -- listed under SC_Kitchen_Cooking_Microwaves_Over_The_Range category
    + see http://www.whirlpool.com/kitchen-1/cooking-2/over-the-range-3/102110018/ and check "over the range" in the left-hand filters
    + include these in the Qualifier's "Hoods" category?
    + https://trello.com/c/S2QpSUyk/11-microwavehoodcombination

## non-field-specific questions and notes

- !* WP calls this category "Hoods", not "Vents", so the API does too
- ?* not hoods, just blowers -- like KAD issue - error?
    + they all have CF hood type = In-Line Blower
    + UXB0600DYS-NAR
    + UXB1200DYS-NAR
    + UXI1200DYS-NAR
    + UXB1200DYS-NAR
    + UXB0600DYS-NAR
- ?* for some models (e.g. UXL5430BSS-NAR) all 3 of islandMount/wallMount/underCabinet are false
    + is this okay? what to do if not?
    + they have hood type = custom hood liner

# Whirlpool Wall Ovens

- X width
- X! capacity - same as KAD
    + no capacity info for 1 of 8: http://www.whirlpool.ca/-[WOS52EM4AS]-1304404/WOS52EM4AS/
- X single - same as KAD
- X double - same as KAD
- X combination - same as KAD
- X trueConvection - same as KAD
- X accuBake (bool) - has SF "AccuBake\u00ae Temperature Management System" (same as Ranges)
- X digitalThermometer - has SF containing "thermometer"
