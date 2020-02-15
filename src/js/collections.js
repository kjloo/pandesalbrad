// Create a new module
var app = angular.module('collectionsPage', []);

app.config(function($locationProvider) {
    $locationProvider.html5Mode(true);
});

app.directive('tooltip', function(){
    return {
        restrict: 'A',
        link: function(scope, element, attrs){
            element.hover(function(){
                // on mouseenter
                element.tooltip('show');
            }, function(){
                // on mouseleave
                element.tooltip('hide');
            });
        }
    };
});

app.factory('shoppingCart', function($http) {
    return {
        loadCollections: function(s) {
            $http.get('/php/loadCollections.php').then(function(response) {
                //console.log(response.data);
                s.collections = response.data;
            });
        },
        loadFormats: function(s) {
            $http.get('/php/loadFormats.php').then(function(response) {
                //console.log(response.data);
                s.formats = response.data;
            });
        },
        loadChoices: function(s) {
            return function(formatID) {
                $http({
                    url: '/php/loadChoices.php',
                    method: "GET",
                    params: {format: formatID}
                 }).then(function(response) {
                    s.choices = response.data;
                });
             };
        },
        count: 0,
        userID: null,
        total: 0.00
    }
});

app.factory('adminUtils', function($http) {
    return {
        isAdmin: function(s) {
            $http.get('/php/isAdmin.php').then(function(response) {
                s.isUserAdmin = response.data.IsAdmin;
            })
        },
        readInput: function(input, callback) {
            fileData = {};
            if (input.files && input.files[0]) {
                var file = input.files[0];
                var reader = new FileReader();
                reader.onload = function(e) {
                    var name = file.name;
                    var type = file.type;
                    var size = ((file.size/(1024*1024)) > 1) ? (file.size/(1024*1024)) + ' mB' : (file.size/1024)+' kB';
                    fileData['data'] = e.target.result;
                    fileData['name'] = name;
                    fileData['type'] = type;
                    fileData['size'] = size;
                    callback.success(fileData);
                };
                reader.readAsDataURL(file);
            } else {
                callback.fail();
            }
        }
    }
});

app.factory('accountLoader', function($http, $window) {
    return {
        getAccountInfo: function(s) {
            return function() {
                $http.get('/php/getAccountInfo.php').then(function(response) {
                    s.account = response.data;
                    if (s.account == "null") {
                        s.account = {
                            "Firstname": "",
                            "Lastname": "",
                            "Email": "",
                        }
                    }
                });
            }
        },
        getShippingInfo: function(s) {
            return function() {
                $http.get('/php/getShippingInfo.php').then(function(response) {
                    //console.log(response.data);
                    s.shipping = response.data;
                    // If nothing is returned, initialize empty values to allow for use in ng-model
                    // For some reason "null" string is returned...
                    if (s.shipping == "null") {
                        s.shipping = {
                            'Address': "",
                            'City': "",
                            'State': [],
                            'StateID': null,
                            'Zipcode': null,
                            'AddressID': null,
                        };
                    }
                });
            }
        },
        loadStates: function(s) {
            return function() {
                $http.get('/php/loadStates.php').then(function(response) {
                    //console.log(response.data);
                    s.states = response.data;
                });
            }
        },
        setAddress: function(s, redirect) {
            // Only if something changed
            if (!s.shippingInfo.$dirty) {
                return;
            }
            var url = "/php/setAddress.php";
            var data = {
                setAddress: true,
                address: s.shipping.Address,
                city: s.shipping.City,
                stateID: s.shipping.State.StateID,
                zipcode: s.shipping.Zipcode,
            };
            if (s.shipping.AddressID != null) {
                data.addressID = s.shipping.AddressID;
            }
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.post(url, jsonToURI(data), config).then(function(response) {
                if (redirect) {
                    $window.location.href = response.data.href;
                } else {
                    s.message = "Shipping Information Updated."
                }
            })
        }
    }
});

app.controller('navbarController', function($http, $scope, $window, shoppingCart, adminUtils) {
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

    $scope.isAdmin = adminUtils.isAdmin($scope);

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

isEmpty = function(o) {
    for(var key in o) {
        if (o.hasOwnProperty(key)) {
            return false;
        }
    }
    return true;
}

// configure controller for sign-up page
app.controller('signupPageController', function($http, $scope, $location) {
    $scope.message = $location.search().message;
    $scope.status = $location.search().status;
    $scope.passwordInfo = null;
    $scope.password = null;
    $scope.password2 = null;
    $scope.submittable = false;

    $scope.token = $location.search().token;
    $scope.validatePassword = function(password) {
        var strongRegex = new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})");
        $scope.submittable = false;
        if (password.length < 10) {
            $scope.passwordInfo = "Password must be at least 10 characters long";
        } else if (!strongRegex.test(password)) {
            $scope.passwordInfo = "Password must be contain upper and lower case letters, a number, and a special character";
        } else if (password != $scope.password2) {
            $scope.passwordInfo = "Passwords do not match";
        } else {
            $scope.submittable = true;
            $scope.passwordInfo = null;
        }
    }
});

// configure controller for login page
app.controller('loginPageController', function($http, $scope, $location) {
    $scope.status = $location.search().login;
    $scope.message = $location.search().message;
});

// configure existing services inside initialization blocks.
app.controller('collectionsPageController', function($http, $scope, $location, adminUtils, shoppingCart) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);

    $scope.status = $location.search().upload;
    $scope.message = $location.search().message;

    $scope.info = null;

    $scope.resetScope = function() {
        $scope.previewData = [];

        $scope.showImage = false;
        $scope.collectionID = null;
        $scope.collectionName = null;
        $scope.imageName = null;
        $scope.collectionIndex = null;
        $scope.selected = null;
    }

    $scope.resetScope();

    $scope.setCollection = function() {
        if (Array.isArray($scope.collections) && $scope.collections.length) {
            for (var key in $scope.collections) {
                var collection = $scope.collections[key];
                if ($scope.selected.CollectionIndex == collection.CollectionIndex) {
                    $scope.collectionID = collection.CollectionID;
                    $scope.collectionName = collection.Name;
                    $scope.imageName = collection.Image;
                    $scope.image = collection.Image;
                    $scope.collectionIndex = collection.CollectionIndex;

                    // Show product image
                    $scope.showImage = true;
                    $scope.previewData['data'] = "/images/" + $scope.image;
                }
            }
        }
    }

    $scope.setSelected = function() {
        if (Array.isArray($scope.collections) && $scope.collections.length) {
            $scope.selected = $scope.collections[0];
            for (var key in $scope.collections) {
                var collection = $scope.collections[key];
                if ($scope.collectionID == collection.CollectionID) {
                    $scope.selected = collection;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['collections'], function () {
        if (Array.isArray($scope.collections) && $scope.collections.length) {
            $scope.collectionID = $scope.collections[0]["CollectionID"];
            $scope.collectionIndex = $scope.collections[0]["CollectionIndex"];
            $scope.setSelected();
            $scope.setCollection();
        }
    });

    $scope.addCollection = function() {
        $scope.resetScope();
        if (Array.isArray($scope.collections)) {
            $scope.collectionIndex = $scope.collections.length;
        } else {
            $scope.collectionIndex = 0;
        }
        // Display message to user
        $scope.info = "Please Upload an Image";
    }
    $scope.deleteCollection = function(collectionID) {
        if (confirm("Are You Sure You Want To Delete?")) {
            var url = "/php/deleteCollection.php/" + collectionID;
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.delete(url, config).then(function(response) {
                $scope.message = "Successfully Deleted Collection.";
                var deleteID = response.data.CollectionID;
                $scope.collections = $scope.collections.filter(function(collection) {
                    return collection.CollectionID != deleteID;
                });
                // Think we need to reload the slides here
                slideUtils.loadSlides($scope);
            });
        }
    }
    $scope.setImage = function(fileData) {
        if (!isEmpty(fileData)) {
            name = fileData["name"];
            $scope.previewData = fileData;
            // Utilizing non angular event call so must inform angular that scope variables are changing.
            $scope.$apply(function() {
                if ($scope.imageName == null) {
                    $scope.imageName = name;
                }
                if ($scope.collectionName == null) {
                    $scope.collectionName = name.substring(0, name.lastIndexOf('.')).replace(/_/g, " ");
                }
                $scope.showImage = true;
                $scope.info = null;
            });
        }
    }
    $scope.removeInput = function() {
        // Unload file
        $("input[type='file']").val("");
        $scope.previewData = [];
        $scope.showImage = false;
    }
    $scope.readInput = function(input) {
        adminUtils.readInput(input, {
            success: $scope.setImage,
            fail: function() {$scope.$apply($scope.removeInput())}
        });
    }    
});

app.controller('productPageController', function($http, $scope, $location, shoppingCart) {
    $scope.item = null;
    $scope.selected = null;
    $scope.options = null;
    $scope.choiceID = null;

    $scope.loadProduct = function() {
        $http({
            url: '/php/loadProducts.php',
            method: "GET",
            params: {product: $location.search().productID}
         }).then(function(response) {
            //console.log(response);
            // This should only return one row of data
            if (response.data.length == 1) {
                $scope.product = response.data[0];
            }
        });
    };

    $scope.loadItems = function() {
        $http({
            url: '/php/loadItems.php',
            method: "GET",
            params: {product: $location.search().productID}
         }).then(function(response) {
            //console.log(response);
            $scope.items = response.data;
        });
    }

    $scope.makeSelection = function() {
        var productID = $location.search().productID;
        var formatID = $scope.selected.FormatID;
        $http({
            url: '/php/getItem.php',
            method: "GET",
            params: {product: productID,
                     format: formatID}
         }).then(function(response) {
            //console.log(response);
            $scope.item = response.data;
        });
        $http({
            url: '/php/getOptions.php',
            method: "GET",
            params: {product: productID,
                     format: formatID}
         }).then(function(response) {
            //console.log(response);
            $scope.options = response.data;
            if ($scope.options.length > 0) {
                $scope.setActiveOption(0, $scope.options[0].ChoiceID);
            } else {

            }
        });
    }

    $scope.setActiveOption = function(optionIndex) {
        $scope.activeOption = optionIndex;
        var productID = $location.search().productID;
        var formatID = $scope.selected.FormatID;
        var choiceID = $scope.options[optionIndex].ChoiceID;
        $http({
            url: '/php/getItem.php',
            method: "GET",
            params: {product: productID,
                     format: formatID,
                     choice: choiceID}
         }).then(function(response) {
            //console.log(response);
            $scope.item = response.data;
        });
    }

    /*$scope.setSelected = function(selection) {
        if (Array.isArray($scope.selections) && $scope.selections.length) {
            $scope.selected = $scope.selections[0];
        }
    }
    $scope.$watchGroup(['selections'], function() {
        for (var key in $scope.selections) {
            var selection = $scope.selections[key];
            $scope.setSelected(selection);
        }
    });*/

    $scope.addToCart = function(itemID) {
        var choiceID = $scope.choiceID;
        var url = "/php/pushCart.php";
        var data = {
            pushCart: true,
            itemID: itemID,
            choiceID: choiceID,
            quantity: 1
        };
        var config = {
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
            }
        };
        $http.post(url, jsonToURI(data), config).then(function(response) {
            shoppingCart.count++;
        });
    }
});

// configure existing services inside initialization blocks.
app.controller('productsPageController', function($http, $scope, $location, $window) {
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

    // Functions for product management
    $scope.openEditPage = function(productID) {
        var url = "upload.html?productID=" + productID;
        $window.location.href = url;
    }

    $scope.deleteProduct = function(productID) {
        // Prompt user with warning
        if (confirm("Are You Sure You Want To Delete?")) {
            var url = "/php/deleteProduct.php/" + productID;
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.delete(url, config).then(function(response) {
                $scope.message = "Successfully Deleted Product.";
                var deleteID = response.data.ProductID;
                $scope.products = $scope.products.filter(function(product) {
                    return product.ProductID != deleteID;
                });
            });
        }
    }

    $scope.message = null;
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

    $scope.updateQuantity = function(itemID, quantity) {
        var url = "/php/updateCart.php";
        var data = {
            updateCart: true,
            itemID: itemID,
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

    $scope.deleteFromCart = function(itemID) {
        var url = "/php/popCart.php";
        var data = {
            popCart: true,
            itemID: itemID
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

app.controller('ordersPageController', function($http, $scope, $location, adminUtils) {
    $scope.message = $location.search().message;
    $scope.isAdmin = adminUtils.isAdmin($scope);

    // Initialize tooltips
    $(document).ready(function(){
      $('[data-toggle="tooltip"]').tooltip();   
    });

    $scope.loadStatuses = function() {
        $http({
            url: "/php/loadStatuses.php",
            method: "GET"
        }).then(function(response) {
            $scope.statuses = response.data;
        });
    }
    $scope.getOrders = function(searchString, status) {
        var statusID = (status != null) ? status.StatusID : null;
        $http({
            url: '/php/getOrders.php',
            method: "GET",
            params: {orderID: searchString,
                     statusID: statusID}
         }).then(function(response) {
            console.log(response);
            $scope.orders = response.data;
        });
    };
    $scope.setSelected = function(order) {
        if (Array.isArray($scope.statuses) && $scope.statuses.length) {
            order.selected = $scope.statuses[0];
            for (var key in $scope.statuses) {
                var status = $scope.statuses[key];
                if (order.StatusID == status.StatusID) {
                    order.selected = status;
                    break;
                }
            }
        }
    }
    $scope.$watchGroup(['statuses', 'orders'], function() {
        for (var key in $scope.orders) {
            var order = $scope.orders[key];
            $scope.setSelected(order);
        }
    });
});

app.controller('usersPageController', function($http, $scope, $location, adminUtils) {
    $scope.message = $location.search().message;

    $scope.isAdmin = adminUtils.isAdmin($scope);
    $scope.loadUsers = function() {
        $http({
            url: '/php/loadUsers.php',
            method: "GET"
         }).then(function(response) {
            //console.log(response);
            $scope.users = response.data;
        });
    };

    $scope.loadRoles = function() {
        $http({
            url: '/php/loadRoles.php',
            method: "GET"
         }).then(function(response) {
            //console.log(response);
            $scope.roles = response.data;
        });
    };

    $scope.getUser = function(searchString) {
        // Textbox is undefined when empty
        if (searchString === undefined) {
            searchString = "";
        }
        $http({
            url: '/php/loadUsers.php/' + searchString,
            method: "GET",
         }).then(function(response) {
            //console.log(response);
            $scope.users = response.data;
        });
    };

    $scope.deleteUser = function(userID) {
        // Prompt user with warning
        if (confirm("Are You Sure You Want To Delete?")) {
            var url = "/php/deleteUser.php/" + userID;
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.delete(url, config).then(function(response) {
                $scope.message = "Successfully Deleted User.";
                $scope.loadUsers();
            });
        }
    }

    $scope.deleteInactiveUsers = function() {
        // Prompt user with warning
        if (confirm("Are You Sure You Want To Delete?")) {
            var url = "/php/deleteUser.php/";
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.delete(url, config).then(function(response) {
                $scope.message = "Successfully Deleted Inactive Users.";
                $scope.loadUsers();
            });
        }
    }

    $scope.setSelected = function(user) {
        if (Array.isArray($scope.roles) && $scope.roles.length) {
            user.selected = $scope.roles[0];
            for (var key in $scope.roles) {
                var role = $scope.roles[key];
                if (user.RoleID == role.RoleID) {
                    user.selected = role;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['roles', 'users'], function() {
        for (var key in $scope.users) {
            var user = $scope.users[key];
            $scope.setSelected(user);
        }
    });

});

app.controller('receiptPageController', function($http, $scope, $location, shoppingCart) {
    $scope.orderID = $location.search().order;
    $scope.shoppingCart = shoppingCart;
});

app.controller('accountPageController', function($http, $scope, accountLoader) {
    $scope.getAccountInfo = accountLoader.getAccountInfo($scope);
});

app.controller('shippingPageController', function($http, $scope, accountLoader) {
    $scope.getShippingInfo = accountLoader.getShippingInfo($scope);
    $scope.loadStates = accountLoader.loadStates($scope);
    $scope.setAddress = function(redirect) {
        accountLoader.setAddress($scope, redirect);
    }

    $scope.setSelected = function() {
        if (Array.isArray($scope.states) && $scope.states.length) {
            $scope.selected = $scope.states[0];
            for (var key in $scope.states) {
                var state = $scope.states[key];
                if ($scope.shipping.StateID == state.StateID) {
                    $scope.shipping.State = state;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['states', 'shipping.StateID'], function () {
        $scope.setSelected();
    });
});

app.controller('checkoutPageController', function($http, $scope, $window, accountLoader, shoppingCart) {
    $scope.message = null;
    // Display 2 decimal places
    $scope.fractionSize = 2;

    // Notify changes
    $scope.customerFlag = false;
    $scope.shippingFlag = false;

    $scope.getAccountInfo = accountLoader.getAccountInfo($scope);
    $scope.getShippingInfo = accountLoader.getShippingInfo($scope);
    $scope.loadStates = accountLoader.loadStates($scope);

    $scope.roundMoney = function(value) {
        return Math.round(value * 100) / 100;
    }

    $scope.calculateTotals = function() {
        // Totals
        if ($scope.shipping != null) {
            $scope.taxTotal = $scope.roundMoney(($scope.total + $scope.shippingTotal) * ($scope.shipping.State.Tax / 100));
            $scope.subtotal = $scope.roundMoney($scope.total + $scope.shippingTotal + $scope.taxTotal);
        }
    }

    $scope.getCheckoutTotal = function() {
        $http.get('/php/getCheckoutTotal.php').then(function(response) {
            //console.log(response.data);
            $scope.shippingTotal = response.data.Shipping;
            $scope.total = response.data.Total;
        });
    }

    $scope.setAddress = function(redirect) {
        if (shoppingCart.userID != null) {
            accountLoader.setAddress($scope, redirect);
        }
    }

    $scope.setSelected = function() {
        if (Array.isArray($scope.states) && $scope.states.length) {
            $scope.selected = $scope.states[0];
            for (var key in $scope.states) {
                var state = $scope.states[key];
                if ($scope.shipping.StateID == state.StateID) {
                    $scope.shipping.State = state;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['states', 'shipping.StateID'], function () {
        $scope.setSelected();
    });

    $scope.$watchGroup(['shipping.State', 'total', 'shippingTotal'], $scope.calculateTotals);

    // Set flags
    $scope.editCustomer = function(flag) {
        $scope.customerFlag = flag;
    }

    $scope.editShipping = function(flag) {
        $scope.shippingFlag = flag;
    }

    // Render the PayPal button into #paypal-button-container
    // This seems to be protected by CORB
    paypal.Buttons({
        style: {
            layout: 'horizontal'
        },
        onInit: function(data, actions) {
            // Disable the buttons
            var submittable = $scope.shippingInfo.$valid && $scope.accountInfo.$valid;
            if (submittable) {
                actions.enable();
            } else {
                actions.disable();
            }
            paypalActions = actions;
            $scope.$watchGroup(['shippingInfo.$valid', 'accountInfo.$valid'], function () {
                var submittable = $scope.shippingInfo.$valid && $scope.accountInfo.$valid;
                if (submittable) {
                    paypalActions.enable();
                } else {
                    paypalActions.disable();
                }
            });
        },
        // Set up the transaction
        createOrder: function(data, actions) {
            return actions.order.create({
                purchase_units: [{
                    amount: {
                        value: $scope.subtotal
                    }
                }]
            });
        },
        // Finalize the transaction
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                var url = "/php/processOrder.php";
                var json = {
                    orderID: data.orderID,
                    fname: $scope.account.Firstname,
                    lname: $scope.account.Lastname,
                    email: $scope.account.Email,
                    address: $scope.shipping.Address,
                    city: $scope.shipping.City,
                    stateID: $scope.shipping.State.StateID,
                    zipcode: $scope.shipping.Zipcode,
                    processOrder: true
                };
                if ($scope.shipping.AddressID) {
                    json.addressID = $scope.shipping.AddressID;
                }
                var config = {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                    }
                };
                $http.post(url, jsonToURI(json), config).then(function(response) {
                    //console.log(response);
                    var result = response.data;
                    if (result.Processed) {
                        $window.location.href = `receipt.html?order=` + result["OrderID"];
                    }
                });

            });
        }
    }).render('#paypal-button-container');
    $scope.shoppingCart = shoppingCart;
});

// configure existing services inside initialization blocks.
app.controller('uploadPageController', function($http, $scope, $location, shoppingCart, adminUtils) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);
    $scope.loadFormats = shoppingCart.loadFormats($scope);
    $scope.loadChoices = shoppingCart.loadChoices($scope);

    $scope.status = $location.search().upload;
    $scope.message = $location.search().message;

    $scope.previewData = [];

    $scope.showImage = false;
    $scope.productID = null;
    $scope.productPrice = null;
    $scope.imageName = null;
    $scope.productName = null;
    $scope.collectionID = null;
    $scope.selected = null;
    $scope.format = null;
    $scope.choices = null;

    $scope.productID = $location.search().productID;
    $scope.getProductInfo = function() {
        if ($scope.productID != null) {
            var url = "/php/getProductInfo.php/" + $scope.productID;
            $http.get(url).then(function(response) {
                $scope.items = response.data;
                $scope.setProductInfo($scope.items[0]);

                // Show product image
                $scope.showImage = true;
                $scope.previewData['data'] = "/images/" + $scope.imageName;
            });
        }
    }

    $scope.setProductInfo = function(product) {
        // Initialize ng-model definitions
        $scope.product = product;
        $scope.productID = $scope.product.ProductID;
        $scope.productPrice = $scope.product.Price;
        $scope.imageName = $scope.product.Image;
        $scope.productName = $scope.product.Name;
        $scope.collectionID = $scope.product.CollectionID;  
    }

    $scope.setItem = function() {
        var formatID = $scope.format.FormatID;
        $scope.loadChoices(formatID);
        if (Array.isArray($scope.items) && $scope.items.length) {
            for (var key in $scope.items) {
                var item = $scope.items[key];
                if (formatID == item.FormatID) {
                    $scope.setProductInfo(item);
                    break;
                }
            }
        }
    }

    $scope.setSelected = function() {
        if (Array.isArray($scope.collections) && $scope.collections.length) {
            $scope.selected = $scope.collections[0];
            for (var key in $scope.collections) {
                var collection = $scope.collections[key];
                if ($scope.collectionID == collection.CollectionID) {
                    $scope.selected = collection;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['collections', 'collectionID'], function () {
        $scope.setSelected();
    });

    $scope.setFormat = function() {
        if (Array.isArray($scope.formats) && $scope.formats.length) {
            $scope.format = $scope.formats[0];
            $scope.setItem();
        }
    }

    $scope.$watchGroup(['formats', 'items'], function () {
        $scope.setFormat();
    });

    $scope.setImage = function(fileData) {
        if (!isEmpty(fileData)) {
            name = fileData["name"];
            $scope.previewData = fileData;
            // Utilizing non angular event call so must inform angular that scope variables are changing.
            $scope.$apply(function() {
                if ($scope.imageName == null) {
                    $scope.imageName = name;
                }
                if ($scope.productName == null) {
                    $scope.productName = name.substring(0, name.lastIndexOf('.')).replace(/_/g, " ");
                }
                $scope.showImage = true;
            });
        }
    }
    $scope.removeInput = function() {
        // Unload file
        $("input[type='file']").val("");
        $scope.previewData = [];
        $scope.showImage = false;
    }
    $scope.readInput = function(input) {
        adminUtils.readInput(input, {
            success: $scope.setImage,
            fail: function() {$scope.$apply($scope.removeInput())}
        });
    }
});

app.controller('activatePageController', function($http, $scope, $location) {
    $scope.message = $location.search().message;
    $scope.activateUser = function() {
        var token = $location.search().token;
        $http({
            url: '/php/activateUser.php/' + token,
            method: "PUT"
         }).then(function(response) {
            //console.log(response);
            $scope.message = response.data ? 'Successfully Activated Account.' : 'Could Not Activate Account.';
        });
    };
});

app.controller('emailPageController', function($http, $scope, $location) {
    $scope.message = $location.search().message;
    $scope.submittable = true;
});

app.controller('recoveryPageController', function($http, $scope, $location) {
    $scope.message = $location.search().message;
    $scope.status = $location.search().status;
});