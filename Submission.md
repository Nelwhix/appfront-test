# Changes made to the Appfront-Test Codebase
- The DatabaseSeeder class was using 10 queries to insert products where a batch insert would be faster and better.
-  on the / route no pagination for products.
- in the getExchangeRate function, while using the curl methods works and is perfect. The laravel http facade reduces the LOC and makes it easier to read
- Added logging for any exceptions when making the call to exchange rate api
- env() function is used outside of config files. It always returns the default value if config is cached.
-  In the product list.blade.php. replace the manual string interpolation for the product’s image with the laravel asset helper
- In the products.show method, product id is automatically injected into the controller, so no need to call the request()->route() method.
- Also I replaced find with findOrFail to handle a situation where the product id does not exist in products.show and admin products.edit
- Used Laravel resourceful controller convention for Admin Products.
- In storing a new product, the use of validator can be replaced with form request class. So my best practice is all form validation is done on a request class and then the controller is now solely for storing the product and other actions
- In storing a new product and editing product, only name should be validated but the required attribute was added to price and description, So removed it.
- in storing a new file, we are the storing the exact file name returned by the client which is wrong, because a new product image name can overwrite an existing one. I used the Storage facade store method instead, it generates a unique id for the filename
- in adding/editing a product, noticed you were called Product::create(), then storing image and now calling product save, which is two queries. reduced it to a single query.
- Didn’t use route model binding because I once encountered an issue with it where on a websocket app, narrate in video…
- Environment variables were pushed to source control which is a security vuln, removed it.
- In the SendPriceChangeNotification and the PriceChangeNotification mailable, updated the class to use php8.1’s constructor property promotion
- Wrong http method were being using for update and deleting of products, replaced it to put/patch and delete respectively.
- Product delete method first loads the product into memory before deleting. Which is unnecessary, replaced it with a delete query.
-  Added a product factory for testing
- Removed the try/catch wrapped around the job dispatch method because it doesn’t throw errors.
- In the console command, updated find to findORFail and added a nice error message.
- Replaced error method with fail method for better terminal formatting
- $product-update() saves the data no need to also call product->save in the console command
- Caching exchange rate to prevent too many api calls
- added accept attribute to the input file types to restrict what can be uploaded.


## Improvements
- From my experience in dealing with money in distributed systems, price of products should be stored in cents and then converted for the UI to prevent precision issues with floats
- Added tests for the add product and update product routes. I like to add tests in areas with large surface area and where different things can occur. To run them: 
```bash
./vendor/bin/pest
```
