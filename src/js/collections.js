// Create a new module
var app = angular.module('collectionsPage', []);

app.config(function($locationProvider) {
    $locationProvider.html5Mode(true);
});

app.factory('shoppingCart', function($http) {
    return {
        loadCollections: function(s) {
            $http.get('/php/loadCollections.php').then(function(response) {
                s.collections = response.data;
            });
        },
        count: 0,
        userID: null
    }
});

app.controller('navbarController', function($http, $scope, $window, shoppingCart) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);
    $scope.getSession = function() {
        $http.get('/php/getSession.php').then(function(response) {
            shoppingCart.userID = response.data.UserID;
            $scope.user = response.data.Username;
            $scope.fname = response.data.Firstname;
            shoppingCart.cart = response.data.Cart;
            for (var key in shoppingCart.cart) {
                shoppingCart.count += shoppingCart.cart[key];
            }
        })
    }

    $scope.searchProducts = function(searchString) {
        // Open products page
        $window.location.href = `products.html?name=${searchString}`;
    }

    $scope.logout = function() {
        $http.get('/php/logout.php').then(function(response) {
            shoppingCart.userID = null;
            $scope.user = null;
            $scope.fname = null;
            shoppingCart.count = 0;

            //redirect to home page
            $window.location.href = "index.html";
        })
    }
    $scope.shoppingCart = shoppingCart;
});

jsonToURI = function(data) {
    var rc = [];
    for (var key in data) {
        rc.push(key + "=" + encodeURI(data[key]));
    }
    return rc.join("&");
};

// configure controller for sign-up page
app.controller('signupPageController', function($http, $scope, $location) {
    $scope.message = $location.search().message;
});

// configure existing services inside initialization blocks.
app.controller('collectionsPageController', function($http, $scope, shoppingCart) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);
});

// configure existing services inside initialization blocks.
app.controller('productsPageController', function($http, $scope, $location, shoppingCart) {
    $scope.loadProducts = function() {
        $http({
            url: '/php/loadProducts.php',
            method: "GET",
            params: {collection: $location.search().collectionID,
                     name: $location.search().name}
         }).then(function(response) {
            //console.log(response);
            $scope.products = response.data;
        });
    };

    $scope.addToCart = function(productID) {
        var url = "/php/pushCart.php";
        var data = {
            pushCart: true,
            productID: productID,
            quantity: 1
        };
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            }
        };
        $http.post(url, jsonToURI(data), config).then(function(response) {
            shoppingCart.count++;
        })
    }
});

app.controller('cartPageController', function($http, $scope, $window, shoppingCart) {
    $scope.loadCart = function() {
        $http.get('/php/loadCart.php').then(function(response) {
            $scope.cart = response.data;
            $scope.total = $scope.cart[$scope.cart.length - 1].Total;
        });
    };


    // Render the PayPal button into #paypal-button-container
    // This seems to be protected by CORB
    paypal.Buttons({
        style: {
            layout: 'horizontal'
        },
        // Set up the transaction
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: $scope.total
                    }
                }]
            });
        },
        // Finalize the transaction
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                // Show a receipt message to the buyer
                $window.location.href = `receipt.html?total=${details.purchase_units[0].amount.value}`;
            });
        }
    }).render('#paypal-button-container');

    $scope.deleteFromCart = function(productID) {
        var url = "/php/popCart.php";
        var data = {
            popCart: true,
            productID: productID
        };
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            }
        };
        $http.post(url, jsonToURI(data), config).then(function(response) {
            var quantity = response.data.Quantity;
            /*$scope.cart = $scope.cart.filter(function(item) {
                return item.ProductID != productID;
            })*/
            // Decrement cart by quantity
            shoppingCart.count -= quantity;
            // Reload cart to get new total
            $scope.loadCart();
        })
    }
});

app.controller('receiptPageController', function($http, $scope, $location, shoppingCart) {
    $scope.printReceipt = function() {
        $scope.total = $location.search().total;
    };
});

app.controller('accountPageController', function($http, $scope) {
    $scope.getAccountInfo = function() {
        $http.get('/php/getAccountInfo.php').then(function(response) {
            $scope.account = response.data;
        });
    };
});

app.controller('shippingPageController', function($http, $scope) {
    $scope.getShippingInfo = function() {
        $http.get('/php/getShippingInfo.php').then(function(response) {
            $scope.account = response.data;
        });
    };
});