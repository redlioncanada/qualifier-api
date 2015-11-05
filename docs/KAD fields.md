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
- X capacity (in cubic feet) (float) - value of CF "Capacity"
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
- X capacity (in cubic feet) (float) 
    + first look at CF containing "Oven Capacity"
        * extract decimal from value
        * if oven is a double/combi and value contains "each oven", use this x 2
    + if not there, get all SFs matching "(decimal) Cu. Ft. Capacity"
        * if double/combi and valueidentifier contains "each oven", use first decimal found x 2
        * otherwise, sum decimals in all results (could be 2)
    + if neither of those found, use SF containing "Total Capacity" - extract $1 from "(\d+(?:\.\d+)?)\s+cu\.?\s+ft\.?"
- X single (bool) - !(combi || double)
- ?*X combi (bool) - name contains "combination"
    + Is this a type of double? Or are "double", "combination", and "single" mutually exclusive?
- X double (bool) - name contains "double"
- X easyConvection (bool)  - has SF "EasyConvect\u2122 Conversion System"
- X tempuratureProbe (bool) - has SF "Temperature Probe"
- X trueConvection (bool) - name/description/has SF that contains "True Convection" (try in that order)


# KitchenAid Vents
- X width (in inches)
    + standard func (reads CompareFeatures, converts fractions to decimals)
    + separately from standard func, check if width is null and if so check product name for /\b(\d+)"\b/ and use $1 if found
- X islandMount - CF "Hood Type" == "Island Mount"
- X wallMount - CF "Hood Type" == "Wall Mount"
- X underCabinet - CF "Hood Type" == "Under-the-Cabinet"
- X CFM (int) - use CF "Fan CFM"
- X exterior - CF "Venting Type" contains "exterior"
- X nonVented - CF "Venting Type" contains "recirculating"
- ?*X convertible - CF "Venting Type" == "Exterior or Recirculating"
    + This means it can be installed as EITHER exterior or non-vented (AKA "recirclulating"), right?
    + When this is true, should exterior and non-vented also be true, or both false?
- X easyConversion - has SF "Easy In-line Conversion"
- X automaticOn - has SF "Automatic Turn On"
- X warmingLamps - has SF containing "Warming Lamp"

## non-field-specific questions

- !* not sure if UXB0600DYS-NAR "600 CFM internal blower" belongs in the vents category
- !* KVUB606DSS-NAR vent is under-the-cabinet type (and will be scored as such) but name says it's island mount: http://www.kitchenaid.ca/en_CA/shop/-[KVUB606DSS]-2104386/KVUB606DSS/