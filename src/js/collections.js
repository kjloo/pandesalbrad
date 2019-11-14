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
        userID: null,
        total: 0.00
    }
});

app.factory('accountLoader', function($http, $window) {
    return {
        getShippingInfo: function(s) {
            $http.get('/php/getShippingInfo.php').then(function(response) {
                //console.log(response.data);
                s.account = response.data;
                // If nothing is returned, initialize empty values to allow for use in ng-model
                // For some reason "null" string is returned...
                if (s.account == "null") {
                    s.account = {
                        'Address': "",
                        'City': "",
                        'State': "",
                        'Zipcode': null
                    };
                }
            });
        },
        setAddress: function(s, redirect) {
            // Only if something changed
            if (!s.shipping.$dirty || !s.addressID) {
                return;
            }
            var url = "/php/setAddress.php";
            var data = {
                setAddress: true,
                address: s.account.Address,
                city: s.account.City,
                state: s.account.State,
                zipcode: s.account.Zipcode,
                addressID: s.account.AddressID
            };
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.post(url, jsonToURI(data), config).then(function(response) {
                if (redirect) {
                    $window.location.href = response.data.href;
                }
            })
        }
    }
});

app.controller('navbarController', function($http, $scope, $window, shoppingCart) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);
    $scope.getSession = function() {
        $http.get('/php/getSession.php').then(function(response) {
            shoppingCart.userID = response.data.UserID;
            $scope.user = response.data.Username;
            $scope.fname = response.data.Firstname;
            shoppingCart.total = response.data.Total;
            shoppingCart.cart = response.data.Cart;
            for (var key in shoppingCart.cart) {
                shoppingCart.count += shoppingCart.cart[key];
            }
        })
    }

    $scope.isAdmin = function() {
        $http.get('/php/isAdmin.php').then(function(response) {
            $scope.isUserAdmin = response.data.IsAdmin;
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
            shoppingCart.total = 0.00;

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

// configure controller for login page
app.controller('loginPageController', function($http, $scope, $location) {
    $scope.status = $location.search().login;
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
            $scope.cart.forEach(function(item, index) {
                item['showUpdate'] = false;
            });
            $scope.total = $scope.cart[$scope.cart.length - 1].Total;
        });
    }; 

    $scope.showUpdate = function(item) {
        item['showUpdate'] = true;
    }

    $scope.updateQuantity = function(productID, quantity) {
        var url = "/php/updateCart.php";
        var data = {
            updateCart: true,
            productID: productID,
            quantity: quantity
        };
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            }
        };
        $http.post(url, jsonToURI(data), config).then(function(response) {
            var quantity = response.data.QuantityDiff;;
            shoppingCart.count += quantity;
            // Reload cart to get new total
            $scope.loadCart();
        })
    }

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

    $scope.openCheckoutPage = function() {
        $window.location.href = 'checkout.html';
    }
});

app.controller('ordersPageController', function($http, $scope) {
    $scope.getOrders = function() {
        $http({
            url: '/php/getOrders.php',
            method: "GET"
         }).then(function(response) {
            //console.log(response);
            $scope.orders = response.data;
        });
    };;
});

app.controller('receiptPageController', function($http, $scope, shoppingCart) {
    $scope.shoppingCart = shoppingCart;
});

app.controller('accountPageController', function($http, $scope) {
    $scope.getAccountInfo = function() {
        $http.get('/php/getAccountInfo.php').then(function(response) {
            $scope.account = response.data;
        });
    };
});

app.controller('shippingPageController', function($http, $scope, accountLoader) {
    $scope.getShippingInfo = accountLoader.getShippingInfo($scope);
    $scope.setAddress = function(redirect) {
        accountLoader.setAddress($scope, redirect);
    }
});

app.controller('checkoutPageController', function($http, $scope, $window, accountLoader, shoppingCart) {
    $scope.getShippingInfo = accountLoader.getShippingInfo($scope);
    $scope.setAddress = function(redirect) {
        accountLoader.setAddress($scope, redirect);
    }
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
                        value: shoppingCart.total
                    }
                }]
            });
        },
        // Finalize the transaction
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                var url = "/php/processOrder.php";
                if (!$scope.account.AddressID) {
                    var json = {
                            orderID: data.orderID,
                            address: $scope.account.Address,
                            city: $scope.account.City,
                            state: $scope.account.State,
                            zipcode: $scope.account.Zipcode,
                            processOrder: true
                    };
                } else {
                    var json = {
                        orderID: data.orderID,
                        addressID: $scope.account.AddressID,
                        processOrder: true
                    };
                }
                var config = {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                    }
                };
                $http.post(url, jsonToURI(json), config).then(function(response) {
                    //console.log(response);
                    if (response.data.Processed) {
                        $window.location.href = `receipt.html`;
                    }
                });

            });
        }
    }).render('#paypal-button-container');
    $scope.shoppingCart = shoppingCart;
});

// configure existing services inside initialization blocks.
app.controller('uploadPageController', function($http, $scope, $location, shoppingCart) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);

    $scope.status = $location.search().upload;
    $scope.message = $location.search().message;

    $scope.showImage = false;
    $scope.previewData = [];
    $scope.imageName;
    $scope.productName;

    $scope.readInput = function(input) {
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                // Utilizing non angular event call so must inform angular that scope variables are changing.
                $scope.$apply(function() {
                    var name = file.name;
                    var type = file.type;
                    var size = ((file.size/(1024*1024)) > 1) ? (file.size/(1024*1024)) + ' mB' : (file.size/1024)+' kB';
                    $scope.previewData['data'] = e.target.result;
                    $scope.previewData['name'] = name;
                    $scope.previewData['type'] = type;
                    $scope.previewData['size'] = size;
                    $scope.imageName = name;
                    $scope.productName = name.substring(0, name.lastIndexOf('.')).replace(/_/g, " ");
                    $scope.showImage = true;
                });
            };
            reader.readAsDataURL(file);
        } else {
            // User didn't upload a file
            $scope.$apply(function() {
                $scope.removeInput();
            });
        }
    }
    $scope.removeInput = function() {
        // Unload file
        $("input[type='file']").val("");
        $scope.previewData = [];
        $scope.showImage = false;
    }
});