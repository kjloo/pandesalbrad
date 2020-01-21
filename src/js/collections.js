// Create a new module
var app = angular.module('collectionsPage', []);

app.config(function($locationProvider) {
    $locationProvider.html5Mode(true);
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
                        'Zipcode': null,
                        'AddressID': null
                    };
                }
            });
        },
        setAddress: function(s, redirect) {
            // Only if something changed
            if (!s.shipping.$dirty) {
                return;
            }
            var url = "/php/setAddress.php";
            var data = {
                setAddress: true,
                address: s.account.Address,
                city: s.account.City,
                state: s.account.State,
                zipcode: s.account.Zipcode,
            };
            if (s.account.AddressID != null) {
                data.push({
                    addressID: s.account.AddressID
                });
            }
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
app.controller('collectionsPageController', function($http, $scope, shoppingCart) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);
});

app.controller('productPageController', function($http, $scope, $location, shoppingCart) {
    $scope.item = null;
    $scope.selected = null;

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
        var url = "/php/pushCart.php";
        var data = {
            pushCart: true,
            itemID: itemID,
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

    $scope.updateQuantity = function(productID, quantity) {
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
            //console.log(response);
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

app.controller('usersPageController', function($http, $scope, $location) {
    $scope.message = $location.search().message;
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
                var deleteID = response.data.UserID;
                $scope.users = $scope.users.filter(function(user) {
                    return user.UserID != deleteID;
                });
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
app.controller('uploadPageController', function($http, $scope, $location, shoppingCart, adminUtils) {
    $scope.loadCollections = shoppingCart.loadCollections($scope);
    $scope.loadFormats = shoppingCart.loadFormats($scope);

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