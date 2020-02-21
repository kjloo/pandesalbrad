// Create a new module
var app = angular.module('mainPage', ['ui.bootstrap', 'collectionsPage']);

app.factory('slideUtils', function($http) {
    return {
        loadSlides: function(s) {
            setSlides = function(slides) {
                s.slides = slides;
            }
            $http({
                url: '/php/loadSlides.php',
                method: "GET"
             }).then(function(response) {
                //console.log(response);
                return setSlides(response.data);
            });
        }
    }
});

app.controller('mainPageController', function($http, $scope, slideUtils) {
    $scope.slides = null;
    $scope.sleep = 5000;
    $scope.loadSlides = slideUtils.loadSlides($scope);
});

app.controller('bannerPageController', function($http, $scope, $location, slideUtils, adminUtils) {
    $scope.adminPermissions = adminUtils.adminPermissions();
    $scope.loadSlides = slideUtils.loadSlides($scope);
    $scope.status = $location.search().upload;
    $scope.message = $location.search().message;

    $scope.info = null;

    $scope.resetScope = function() {
        $scope.previewData = [];

        $scope.showImage = false;
        $scope.slideID = null;
        $scope.slideName = null;
        $scope.caption = null;
        $scope.imageName = null;
        $scope.link = null;
        $scope.slideIndex = null;
        $scope.selected = null;
    }

    $scope.resetScope();

    $scope.setSlide = function() {
        if (Array.isArray($scope.slides) && $scope.slides.length) {
            for (var key in $scope.slides) {
                var slide = $scope.slides[key];
                if ($scope.selected.SlideIndex == slide.SlideIndex) {
                    $scope.slideID = slide.SlideID;
                    $scope.slideName = slide.Name;
                    $scope.caption = slide.Caption;
                    $scope.imageName = slide.Image;
                    $scope.image = slide.Image;
                    $scope.link = slide.Link;
                    $scope.slideIndex = slide.SlideIndex;

                    // Show product image
                    $scope.showImage = true;
                    $scope.previewData['data'] = "/images/" + $scope.image;
                }
            }
        }
    }

    $scope.setSelected = function() {
        if (Array.isArray($scope.slides) && $scope.slides.length) {
            $scope.selected = $scope.slides[0];
            for (var key in $scope.slides) {
                var slide = $scope.slides[key];
                if ($scope.slideID == slide.SlideID) {
                    $scope.selected = slide;
                    break;
                }
            }
        }
    }

    $scope.$watchGroup(['slides'], function () {
        if (Array.isArray($scope.slides) && $scope.slides.length) {
            $scope.slideID = $scope.slides[0]["SlideID"];
            $scope.slideIndex = $scope.slides[0]["SlideIndex"];
            $scope.setSelected();
            $scope.setSlide();
        }
    });

    $scope.addSlide = function() {
        $scope.resetScope();
        if (Array.isArray($scope.slides)) {
            $scope.slideIndex = $scope.slides.length;
        } else {
            $scope.slideIndex = 0;
        }
        // Display message to user
        $scope.info = "Please Upload an Image";
    }
    $scope.deleteSlide = function(slideID) {
        if (confirm("Are You Sure You Want To Delete?")) {
            var url = "/php/deleteSlide.php/" + slideID;
            var config = {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8'
                }
            };
            $http.delete(url, config).then(function(response) {
                $scope.message = "Successfully Deleted Slide.";
                var deleteID = response.data.SlideID;
                $scope.slides = $scope.slides.filter(function(slide) {
                    return slide.SlideID != deleteID;
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
                if ($scope.slideName == null) {
                    $scope.slideName = name.substring(0, name.lastIndexOf('.')).replace(/_/g, " ");
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