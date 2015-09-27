To search for remaining action items: ^-\s+(?!X)

X = implemented somehow
! = flag warning to RLC
? = question for RLC/WP

# KitchenAid Dishwasher
- X bottleWash - SalesFeature exists
- X proDry - SalesFeature exists
- ! placeSettings
     + they've removed this from the feed. e.g. KDFE104DSS-NAR had it as a comparefeature under config/overview as of july 26, but now gone for all.
- X proScrub - SalesFeature exists
- X proWash - SalesFeature exists
- X cleanWater - SalesFeature exists
- !X decibels
    + "(\d+) dBA" in name, not all have it (KDFE104DSS-NAR doesn't)
- ? culinary Caddy
    + can't find, closest thing is "Utility Basket (Upper Rack)" e.g. http://www.kitchenaid.com/shop/major-appliances-1/dishwashers-2/dishwashers-3/-[KDTM354ESS]-408526/KDTM354ESS/
- X thirdLevelRack - SalesFeature exists
- X pocketHandleConsole - "Pocket Handle" in name
- ?X FID
    + used group:
        SC_Major_Appliances_Dishwashers_Dishwashers_Fully_Integrated
        similar to this one for MTG:
        SC_Kitchen_Dishwashers_and_Kitchen_Cleaning_Dishwashers_BuiltIn_Fully_integrated_Console
- X panelReady - SalesFeature exists

# KitchenAid Fridge
- width (in inches) (int)
- height (in inches) (int)
- energyStar
- volume
- topMount 
- bottomMount
- frenchDoor5
- frenchDoor 
- indoorDispenser
- filtered
- exteriorDispenser
- indoorIce
- standardDepth
- counterDepth
- builtIn
- producerPreserver  
- extendFreshPlus
- freshChill
- preservaCare 
- extendFresh 
- freshChill
- maxCool

# KitchenAid Cooktops
- width
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
- width (in inches) (int)
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
- width (in inches) (int)
- volume (in cubic feet) (float) 
- single (bool)
- combi (bool)
- double (bool)
- easyConvection (bool)  
- tempuratureProbe (bool)
- trueConvection (bool)


# KitchenAid Vents
- width (in inches) (int)
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







