CREATE TABLE roles(
    RoleID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Role VARCHAR(255) NOT NULL
);

INSERT INTO roles(Role) VALUES("Admin"), ("Customer");

CREATE TABLE states(
    StateID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(14) NOT NULL UNIQUE,
    Abbreviation VARCHAR(2) NOT NULL UNIQUE,
    Tax DECIMAL(6,3) NOT NULL,
);

INSERT INTO states(Name, Abbreviation, Tax)
VALUES("Alabama", "AL", 4),
("Alaska", "AK", 0),
("Arizona", "AZ", 5.6),
("Arkansas", "AR", 6.5),
("California", "CA", 7.25),
("Colorado", "CO", 2.9),
("Connecticut", "CT", 6.35),
("Delaware", "DE", 0),
("Florida", "FL", 6),
("Georgia", "GA", 4),
("Hawaii", "HI", 4),
("Idaho", "ID", 6),
("Illinois", "IL", 6.25),
("Indiana", "IN", 7),
("Iowa", "IA", 6),
("Kansas", "KS", 6.5),
("Kentucky", "KY", 6),
("Louisiana", "LA", 4.45),
("Maine", "ME", 5.5),
("Maryland", "MD", 6),
("Massachusetts", "MA", 6.25),
("Michigan", "MI", 6),
("Minnesota", "MN", 6.875),
("Mississippi", "MS", 7),
("Missouri", "MO", 4.225),
("Montana", "MT", 0),
("Nebraska", "NE", 5.5),
("Nevada", "NV", 6.85),
("New Hampshire", "NH", 0),
("New Jersey", "NJ", 6.625),
("New Mexico", "NM", 5.125),
("New York", "NY", 4),
("North Carolina", "NC", 4.750),
("North Dakota", "ND", 5),
("Ohio", "OH", 5.75),
("Oklahoma", "OK", 4.5),
("Oregon", "OR", 0),
("Pennsylvania", "PA", 6),
("Rhode Island", "RI", 7),
("South Carolina", "SC", 6),
("South Dakota", "SD", 4.5),
("Tennessee", "TN", 7),
("Texas", "TX", 6.25),
("Utah", "UT", 4.85),
("Vermont", "VT", 6),
("Virginia", "VA",4.3),
("Washington", "WA", 6.5),
("West Virginia", "WV", 6),
("Wisconsin", "WI", 5),
("Wyoming", "WY", 4);

CREATE TABLE coupons(
    Code VARCHAR(32) NOT NULL PRIMARY KEY,
    Discount DECIMAL(6,3) NOT NULL,
    Active BOOLEAN NOT NULL
);

CREATE TABLE users(
    UserID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(255) NOT NULL,
    Firstname VARCHAR(255) NOT NULL,
    Lastname VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Password VARCHAR(60) NOT NULL,
    SignupDate DATETIME NOT NULL,
    Activated BOOLEAN NOT NULL,
    Token VARCHAR(255),
    RoleID INT NOT NULL,
    FOREIGN KEY(RoleID) REFERENCES roles(RoleID)
);

CREATE TABLE collections(
    CollectionID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL,
    CollectionIndex INT NOT NULL
);

INSERT INTO collections(Name, Image)
VALUES("Happa Shakas", "HappaShaka.png", 0),
("Head Hunter", "HeadHunter.png", 1),
("Star Wars", "StarWars.png", 2),
("Illustrations", "Illustrations.png", 3),

CREATE TABLE categories(
    CategoryID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL
);
INSERT INTO categories(Name, Image)
VALUES("Disney", "Disney.png"),
("Pokemon", "Pokemon.png"),
("Star Wars", "StarWars.png"),
("Anime", "Anime.png"),
("Cartoons", "Cartoons.png"),
("Comics", "Comics.png"),
("Video Games", "VideoGames.png"),
("TV Shows", "TVShows.png"),
("Filipino", "Filipino.png"),
("Hawaii", "Hawaii.png");

CREATE TABLE products(
    ProductID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL,
    CollectionID INT NOT NULL,
    FOREIGN KEY(CollectionID) REFERENCES collections(CollectionID)
);

CREATE TABLE product_categories(
    ProductID INT NOT NULL,
    CategoryID INT NOT NULL,
    PRIMARY KEY(ProductID, CategoryID),
    FOREIGN KEY(ProductID) REFERENCES products(ProductID) ON DELETE CASCADE,
    FOREIGN KEY(CategoryID) REFERENCES categories(CategoryID) ON DELETE CASCADE
);

CREATE TABLE options(
    OptionID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL
);

INSERT INTO options(Name) Values("Size"), ("Color");

CREATE TABLE choices(
    ChoiceID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    OptionID INT NOT NULL,
    FOREIGN KEY(OptionID) REFERENCES options(OptionID) ON DELETE CASCADE
);

INSERT INTO choices(Name, OptionID) VALUES("Small", (SELECT OptionID FROM options WHERE Name = "Size"));
INSERT INTO choices(Name, OptionID) VALUES("Medium", (SELECT OptionID FROM options WHERE Name = "Size"));
INSERT INTO choices(Name, OptionID) VALUES("Large", (SELECT OptionID FROM options WHERE Name = "Size"));
INSERT INTO choices(Name, OptionID) VALUES("Extra Large", (SELECT OptionID FROM options WHERE Name = "Size"));

CREATE TABLE shipping(
    ShippingID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(32) NOT NULL,
    Cost DECIMAL(6,2) NOT NULL,
    Bundle INT NOT NULL
);

INSERT INTO shipping(Name, Cost, Bundle) VALUES("Clothes", 7.00, 2), ("Sticker", 0.49, 0);

CREATE TABLE formats(
    FormatID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    ShippingID INT NOT NULL,
    FOREIGN KEY(ShippingID) REFERENCES shipping(ShippingID)
);

CREATE TABLE format_options(
    FormatID INT NOT NULL,
    OptionID INT NOT NULL,
    FOREIGN KEY(FormatID) REFERENCES formats(FormatID) ON DELETE CASCADE,
    FOREIGN KEY(OptionID) REFERENCES options(OptionID) ON DELETE CASCADE
);

INSERT INTO formats(Name, Shipping) VALUES("Sticker", 7.00), ("T-Shirt", 0.49);

INSERT INTO format_options(FormatID, OptionID)
VALUES((SELECT FormatID FROM formats WHERE Name = "T-Shirt"), (SELECT OptionID FROM options WHERE Name = "Size"));

CREATE TABLE items(
    ItemID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    FormatID INT NOT NULL,
    ChoiceID INT,
    Price DECIMAL(6,2) NOT NULL,
    FOREIGN KEY(ProductID) REFERENCES products(ProductID),
    FOREIGN KEY(FormatID) REFERENCES formats(FormatID) ON DELETE CASCADE,
    FOREIGN KEY(ChoiceID) REFERENCES choices(ChoiceID) ON DELETE CASCADE
);

CREATE TABLE addresses(
    AddressID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Address VARCHAR(255) NOT NULL,
    City VARCHAR(255) NOT NULL,
    StateID INT NOT NULL,
    Zipcode VARCHAR(5) NOT NULL,
    UserID INT,
    FOREIGN KEY(UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY(StateID) REFERENCES states(StateID)
);

CREATE TABLE statuses(
    StatusID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Status VARCHAR(32) NOT NULL
);

INSERT INTO statuses(Name) VALUES("Ordered"), ("Proccessing"), ("Shipped"), ("Delivered"), ("Returned"), ("Cancelled");

CREATE TABLE orders(
    OrderID VARCHAR(255) NOT NULL PRIMARY KEY,
    UserID INT,
    StatusID INT NOT NULL,
    OrderDate DATETIME NOT NULL,
    Total DECIMAL(6,2) NOT NULL,
    Address VARCHAR(255) NOT NULL,
    City VARCHAR(255) NOT NULL,
    StateID INT NOT NULL,
    Zipcode VARCHAR(5) NOT NULL,
    Firstname VARCHAR(255) NOT NULL,
    Lastname VARCHAR(255) NOT NULL,
    Email VARCHAR(255) NOT NULL,
    Coupon VARCHAR(32),
    FOREIGN KEY(UserID) REFERENCES users(UserID),
    FOREIGN KEY(StatusID) REFERENCES statuses(StatusID),
    FOREIGN KEY(StateID) REFERENCES states(StateID),
    FOREIGN KEY(Coupon) REFERENCES coupons(Code)
);

CREATE TABLE packages(
    OrderID VARCHAR(255) NOT NULL, 
    ItemID INT NOT NULL,
    Quantity INT NOT NULL,
    FOREIGN KEY(OrderID) REFERENCES orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY(ItemID) REFERENCES items(ItemID)
);

CREATE TABLE carts(
    Quantity INT NOT NULL,
    UserID INT NOT NULL,
    ItemID INT NOT NULL,
    FOREIGN KEY(UserID) REFERENCES users(UserID) ON DELETE CASCADE,
    FOREIGN KEY(ItemID) REFERENCES items(ItemID) ON DELETE CASCADE
);

CREATE TABLE slides(
    SlideID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    SlideIndex INT NOT NULL,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL,
    Caption VARCHAR(255),
    Link VARCHAR(255)
);