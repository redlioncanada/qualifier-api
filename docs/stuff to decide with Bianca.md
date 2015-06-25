# stuff to decide with Bianca

## categories

from feed data, you can get the direct category a product is in, and its parent cats, recursively. however, the depth of nesting is inconsistent. and top-level cat is too general for "appliance" field; it's more like what room is it in, e.g. "Kitchen".

proposal: we could decide case-by-case, for all categories that are relevant to the qualifier app, which category names from the feed will result in which categories in the API response.

## prices

<listprice> or <saleprice>? prob question for WP

prices are per-variant. some variants are a bit extra, e.g. stainless steel for YMET8720DS, prob b/c it looks the best. should i give variant prices as-is, for each variant? or just give lowest price for product overall?

## name/description

is listed for both top-level products and their variants. include both or just one?

## general

in general, use more meaningful keys instead of sequential? e.g. sku, colour code