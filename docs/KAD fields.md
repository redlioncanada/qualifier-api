# Legend
/ = defined somehow (can be implemented) but not yet implemented
X = implemented somehow
! = flag warning to RLC
? = question for RLC/WP
* = waiting to hear back

# Regex Searches

- All action items: ^-\s+(?!X\s)
- Something's ready to implement (questions may remain): ^-\s+[!?*]*/[!?*]*\s+
- Items to communicate about: ^-\s+[/X]*[!?]+[/X!?]*\s+
- Items waiting to hear back: ^-\s+[/X]*\*+[/X!?*]*\s+

# KitchenAid Dishwasher
- X bottleWash - SalesFeature exists
- X proDry - SalesFeature exists
- *! placeSettings
     + they've removed this from the feed. e.g. KDFE104DSS-NAR had it as a comparefeature under config/overview as of july 26, but now gone for all.
- X proScrub - SalesFeature exists
- X proWash - SalesFeature exists
- X cleanWater - SalesFeature exists
- X decibels - use compare feature "Decibel Level (dBA)"
- X culinaryCaddy - SalesFeature exists
- X thirdLevelRack - SalesFeature exists
- X pocketHandleConsole - "Pocket Handle" in name
- X FID
    + used group:
        SC_Major_Appliances_Dishwashers_Dishwashers_Fully_Integrated
        similar to this one for MTG:
        SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers_BuiltIn_Fully_integrated_Console
- X panelReady - SalesFeature exists

# KitchenAid Fridge
- X width (in inches) (int)
- X height (in inches) (int)
    + both with standard func (reads CompareFeatures, converts fractions to decimals)
- X energyStar - compare feature exists, or sales feature exists
- X capacity
    + "Total Capacity" comparefeature
- X topMount - same as MTG
- X bottomMount - same as MTG
- X 5door (originally frenchDoor5)
    + "5-Door Configuration" salesfeature exists
- X frenchDoor - same as MTG
- ?*X indoorDispenser - same as MTG - comparefeature "Dispenser Type" not 'no dispenser' - just confirm this and the rest of the dispenser-related items
- ?*X filtered - comparefeature = 'Yes'
- ?*X exteriorDispenser - comparefeature "Dispenser Type" has 'exterior'
- ?*X indoorIce - comparefeature "Dispenser Type" has 'ice'
- X standardDepth - default if not (counterDepth or builtIn)
- X counterDepth - "counter[- ]depth" in name or "counter-depth" salesfeature exists
- X builtIn - "built[- ]in" in name
- X producePreserver - "Produce Preserver" salesfeature exists
- X extendFreshPlus - salesfeature exists: "ExtendFresh\u2122 Temperature Management System"
- X freshChill
- X preservaCare 
- X extendFresh - salesfeature exists: "ExtendFresh\u2122 Plus Temperature Management System"
- X maxCool

# KitchenAid Cooktops
- X width - standard func (reads CompareFeatures, converts fractions to decimals)
- X induction (bool)
- X electric (bool)
- X gas (bool)
- / 5Elements (bool) - delete
- X 5Burners (bool)  - check "Number of Elements-Burners" comparefeature
- X 6Burners (bool) - check "Number of Elements-Burners" comparefeature
- / cookShield (bool) - "CookShield Finish" salesfeature
- / touchActivated (bool) - name/descr contains "Touch[ -]Activated Controls" or has SF "Touch-Activated Electronic Controls"
- / meltAndHold (bool) - has "Melt and Hold" SF
- ?* electricEvenHeat (bool) - "even-heat" in description AND is electric
    + confirm interpretation
- / inductionSimmer (bool) - "Simmer Function" SF AND is induction type
- / performanceBoost (bool) - SF "Performance Boost"
- / 5KBTUSimmer (bool) - SF: 5 or 6 + "K BTU Even-Heat\u2122 Simmer Burner" -- assuming 6 also counts
- / 15KBTU (bool) - has SF _containing_ "15K BTU"
- / 18KBTUEvenHeat (bool) - "18K BTU Even-Heat\u2122 Gas Grill" SF
- ?* 20KBTUDual (bool) - https://trello.com/c/BqanyTSw
    + which salesfeature is it?
        + "20K BTU Professional Dual Ring Burner" or
        + "20K BTU Ultra Power\u2122 Dual-Flame Burner"
        + or either?

# KitchenAid Ranges
- X width (in inches) - standard func (reads CompareFeatures, converts fractions to decimals)
- / capacity (in cubic feet) (float) - value of CF "Capacity"
- / warmingDrawer (bool) has SF "Warming Drawer" OR name contains "Warming Drawer"
- / aquaLift (bool) - has SF "Aqualift\u00ae"
- / trueConvection (bool) - has SF "Even-Heat\u2122 True Convection"
- / temperatureProbe (bool)
    +  value of CF :
                        "description": "Controls",
                        "valueidentifier": "Selections",
            contains "Temperature Probe"
- / wirelessProbe (bool) - has SF "Wireless Probe"
- / steamRack (bool) - has SF "Steam Rack"
- / bakingDrawer (bool) - has SF "Baking drawer"
- /*? gas (bool) - CF "Fuel Type" is "Electric" or "Dual Fuel"
- /*? electric (bool) - CF "Fuel Type" is "Gas" or "Dual Fuel"
    + confirm both should be true for dual fuel
- / evenHeat (bool) - has SF _containing_ "Even-Heat\u2122 Ultra Element" - applies to cooktop part, not oven
- ?* 5KBTUSimmer (bool) 
    + can't find separately from 5KBTUSimmerMelt
- / 5KBTUSimmerMelt (bool) - has SF "5K BTU Simmer\/Melt Burner - Reduces to 500 BTUs"
- / 15KBTU (bool) - has at least one burner that is at least 15K BTU:
    + use presence of CF matching:
        "description": "Cooktop Features",
        "valueidentifier": "******-Burner Power",
        "value": (>= 15,000) " BTU",
- !*/ 20KBTUDual (bool) - has SF "20K BTU Ultra Power\u2122 Dual-Flame Burner" (for gas) and _guessing_ for electric it's "20K BTU Professional Dual Ring Burner", although there are no examples currently.
- X double (bool) - name contains "double"
- / 5BurnersElements - check "Number of Cooking Element-Burners" CF
- / 6BurnersElements - check "Number of Cooking Element-Burners" CF


# KitchenAid Wall Ovens
- X width (in inches) - standard func (reads CompareFeatures, converts fractions to decimals)
- capacity (in cubic feet) (float) 
- single (bool)
- combi (bool)
- double (bool)
- easyConvection (bool)  
- tempuratureProbe (bool)
- trueConvection (bool)


# KitchenAid Vents
- !X width (in inches)
    + standard func (reads CompareFeatures, converts fractions to decimals)
    + some without dimenions (5 out of 38). e.g. http://www.kitchenaid.ca/en_CA/shop/major-appliances-1/hoods-and-vents-2/hoods-and-vents-3/-[KVIB606DBS]-5568101/KVIB606DBS/
- islandMount
- wallMount
- underCabinet 
- CFM (int)
- exterior
- nonVented
- convertible
- easyConversion
- automaticOn
- warmingLamps

