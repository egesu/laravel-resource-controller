# laravel-resource-controller
Simple base controller for resource controllers with index, show, store, update and destroy methods.

#parameters
All models accept both models and id's. If you pass id, then controller will look for the model and if it's not found, return not found error.

#edit method
It's used for sorting. There should be a method with name "sort" in the model for this method to work. Sort method should receive old index and the new index for the model. I will add this method to the controller when I have time.

Pull requests and comments are welcome.

#example
    <?php
    
    namespace Api\Provider;
    
    class CustomerInfoRequestsController extends \Api\ApiController
    {
        // this is our model
        protected $baseClass = '\DugunV2\Models\Provider\CustomerInfoRequest';
        
        // set property to order your results in index method
        protected $orderBy = 'sentDate';
    
        // direction of sort
        protected $orderByDirection = 'DESC';
    
        // pagination for index method. set to false to disable pagination
        protected $paginate = 30;
    
        // We override index method to add some filters
        public function index()
        {
            parent::index();
    
            if(isset($this->filters['customer_id']) and $this->filters['customer_id'] > 0) {
                // You can use any query builder method in $this->query
                $this->query->join('providers AS p', 'p.id', '=', 'infoReqStore.provId')
                    ->where('customer_id', $this->filters['customer_id']);
            }
        }
    }
