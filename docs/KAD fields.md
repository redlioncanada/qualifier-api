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
- Items waiting to hear back: ^-\s+[/X]*\*+[/X!?*]*\s+

# KitchenAid Dishwasher
- X bottleWash - SalesFeature exists
- X proDry - SalesFeature exists
- X placeSettings - "(decimal) place settings" of CF "capacity"
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
- X indoorDispenser - same as MTG - comparefeature "Dispenser Type" not 'no dispenser'
- X filtered - comparefeature = 'Yes'
- X exteriorDispenser - comparefeature "Dispenser Type" has 'exterior'
- X indoorIce - comparefeature "Dispenser Type" has 'ice'
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
- X induction (bool) - name contains "induction"
- X electric (bool) - name contains "electric"
- X gas (bool) - name contains "gas"
- X 5Burners (bool)  - check "Number of Elements-Burners" comparefeature
- X 6Burners (bool) - check "Number of Elements-Burners" comparefeature
- X cookShield (bool) - has "CookShield Finish" salesfeature
- X touchActivated (bool) - name/descr contains "Touch[ -]Activated Controls" or has SF "Touch-Activated Electronic Controls"
- X meltAndHold (bool) - has "Melt and Hold" SF
- X electricEvenHeat (bool) - is electric type AND has "even-heat" in description
- X inductionSimmer (bool) - is induction type AND has "Simmer Function" SF
- X performanceBoost (bool) - has SF "Performance Boost"
- X 5KBTUSimmer (bool) - has SF: 5 or 6 + "K BTU Even-Heat\u2122 Simmer Burner" -- 6 also counts
- X 15KBTU (bool) - has SF _containing_ "15K BTU"
- X 18KBTUEvenHeat (bool) - has "18K BTU Even-Heat\u2122 Gas Grill" SF
- X 20KBTUDual (bool) - has at least one of these SFs:
        + "20K BTU Professional Dual Ring Burner"
        + "20K BTU Ultra Power\u2122 Dual-Flame Burner"

# KitchenAid Ranges
- X width (in inches) - standard func (reads CompareFeatures, converts fractions to decimals)
- !X capacity (in cubic feet) (float) - value of CF "Capacity"
    + flag that it's a total for doubles
- X warmingDrawer (bool) has SF "Warming Drawer" OR name contains "Warming Drawer"
- X aquaLift (bool) - has SF "Aqualift\u00ae"
- X trueConvection (bool) - has SF "Even-Heat\u2122 True Convection"
- X temperatureProbe (bool)
    +  value of CF :
                        "description": "Controls",
                        "valueidentifier": "Selections",
            contains "Temperature Probe"
- X wirelessProbe (bool) - has SF "Wireless Probe"
- X steamRack (bool) - has SF "Steam Rack"
- X bakingDrawer (bool) - has SF "Baking drawer"
- X gas (bool) - CF "Fuel Type" is "Gas" or "Dual Fuel"
- X electric (bool) - CF "Fuel Type" is "Electric" only - false for "Dual Fuel"
- X evenHeat (bool) - has SF _containing_ "Even-Heat\u2122 Ultra Element" - applies to cooktop part, not oven
- X 5KBTUSimmerMelt (bool) - has SF "5K BTU Simmer\/Melt Burner - Reduces to 500 BTUs"
- X 15KBTU (bool) - has at least one burner that is at least 15K BTU:
    + use presence of CF matching:
        "description": "Cooktop Features",
        "valueidentifier": "****** Element-Burner Power",
        "value": (>= 15,000) " BTU",
- X 20KBTUDual (bool) - same as for cooktops - has at least one of these SFs:
        + "20K BTU Professional Dual Ring Burner" (no examples of this currently in Ranges, but since some Cooktops have it, and we're using it for this field there, might as well put it here too)
        + "20K BTU Ultra Power\u2122 Dual-Flame Burner"
- X double (bool) - name contains "double"
- X 5Burners - check "Number of Cooking Element-Burners" CF (originally '5BurnersElements')
- X 6Burners - check "Number of Cooking Element-Burners" CF (originally '6BurnersElements')


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
- X width (in inches)
    + standard func (reads CompareFeatures, converts fractions to decimals)
    + separately from standard func, check if width is null and if so check product name for /\b(\d+)"\b/ and use $1 if found
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

