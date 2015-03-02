# laravel-resource-controller
Simple base controller for resource controllers with index, show, store, update and destroy methods.

#parameters
All models accept both models and id's. If you pass id, then controller will look for the model and if it's not found, return not found error.

#edit method
It's used for sorting. There should be a method with name "sort" in the model for this method to work. Sort method should receive old index and the new index for the model. I will add this method to the controller when I have time.

Pull requests and comments are welcome.
