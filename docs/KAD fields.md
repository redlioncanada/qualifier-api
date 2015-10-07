To search for remaining action items: ^-\s+(?!X\s)

X = implemented somehow
! = flag warning to RLC
? = question for RLC/WP
* = waiting to hear back

# KitchenAid Dishwasher
- X bottleWash - SalesFeature exists
- X proDry - SalesFeature exists
- *! placeSettings
     + they've removed this from the feed. e.g. KDFE104DSS-NAR had it as a comparefeature under config/overview as of july 26, but now gone for all.
- X proScrub - SalesFeature exists
- X proWash - SalesFeature exists
- X cleanWater - SalesFeature exists
- *!X decibels
    + "(\d+) dBA" in name, not all have it (KDFE104DSS-NAR doesn't)
- ? culinary Caddy - found, but only for KDTM704EBS-NAR. checking with Chris that this is right
    + can't find, closest thing is "Utility Basket (Upper Rack)" e.g. http://www.kitchenaid.com/shop/major-appliances-1/dishwashers-2/dishwashers-3/-[KDTM354ESS]-408526/KDTM354ESS/
    + score for utility basket - JB
    + email thread: https://mail.google.com/mail/u/0/#search/from%3Achris.taylor%40redlioncanada.com+OR+to%3Achris.taylor%40redlioncanada.com/15015457d2e316c0
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
- !X volume
    + "Total Capacity" comparefeature - flag to Bianca I kept it named 'capacity' for consistency with maytag fridges
- X topMount - same as MTG
- X bottomMount - same as MTG
- !X frenchDoor5
    + "5-Door Configuration" salesfeature exists
    + flag to Bianca that I called it '5door' because it doesn't seem necesarily related to french door
- X frenchDoor - same as MTG
- ?X indoorDispenser - same as MTG - comparefeature "Dispenser Type" not 'no dispenser' - just confirm this and the rest of the dispenser-related items
- ?X filtered - comparefeature = 'Yes'
- ?X exteriorDispenser - comparefeature "Dispenser Type" has 'exterior'
- ?X indoorIce - comparefeature "Dispenser Type" has 'ice'
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
- induction (bool)
- electric (bool)
- gas (bool)
- 5Elements (bool)  
- 5Burners (bool)  
- 6Burners (bool) 
- dishwasherSafeKnobs (bool) 
- cookShield (bool) 
- touchActivated (bool)
- electricInduction (bool)
- meltAndHold (bool)
- electricEvenHeat (bool) 
- inductionSimmer (bool) 
- performanceBoost (bool)
- 5KBTUSimmerMelt (bool) 
- 5KBTUSimmer (bool)
- 15KBTU (bool)
- 18KBTUEvenHeat (bool)
- 20KBTUDual (bool)  

# KitchenAid Ranges
- X width (in inches) (int) - standard func (reads CompareFeatures, converts fractions to decimals)
- volume (in cubic feet) (float)
- warmingDrawer (bool) 
- aquaLift (bool)
- trueConvection (bool)
- temperatureProbe (bool)
- wirelessProbe (bool)
- steamRack (bool)
- bakingDrawer (bool)
- gas (bool) 
- evenHeat (bool)
- inductionSimmer (bool) 
- performanceBoost (bool)
- meltAndHold (bool)
- electricEvenHeat (bool)
- 5KBTUSimmer (bool) 
- 5KBTUSimmerMelt (bool) 
- 15KBTU (bool)  
- 18KBTUEvenHeat (bool)
- 20KBTUDual (bool)  
- double (bool)
- 5BurnersElements 
- 6BurnersElements
- electric


# KitchenAid Wall Ovens
- X width (in inches) (int) - standard func (reads CompareFeatures, converts fractions to decimals)
- volume (in cubic feet) (float) 
- single (bool)
- combi (bool)
- double (bool)
- easyConvection (bool)  
- tempuratureProbe (bool)
- trueConvection (bool)


# KitchenAid Vents
- !X width (in inches) (int)
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

