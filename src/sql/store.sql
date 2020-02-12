CREATE TABLE roles(
    RoleID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Role VARCHAR(255) NOT NULL
);

INSERT INTO roles(Role) VALUES("Admin"), ("Customer");

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
VALUES("Disney", "Disney.png", 0),
("Pokemon", "Pokemon.png", 1),
("Star Wars", "StarWars.png", 2),
("Anime", "Anime.png", 3),
("Cartoons", "Cartoons.png", 4),
("Comics", "Comics.png", 5),
("Illustrations", "Illustrations.png", 6),
("Video Games", "VideoGames.png", 7),
("TV Shows", "TVShows.png", 8),
("Filipino", "Filipino.png", 9),
("Hawaii", "Hawaii.png", 10);

CREATE TABLE products(
    ProductID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL,
    CollectionID INT NOT NULL,
    FOREIGN KEY(CollectionID) REFERENCES collections(CollectionID) ON DELETE CASCADE
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

CREATE TABLE formats(
    FormatID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Shipping DECIMAL(6,2)
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
    FOREIGN KEY(ProductID) REFERENCES products(ProductID) ON DELETE CASCADE,
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
    FOREIGN KEY(StateID) REFERENCES states(StateID) ON DELETE CASCADE
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
    FOREIGN KEY(UserID) REFERENCES users(UserID),
    FOREIGN KEY(StatusID) REFERENCES statuses(StatusID),
    FOREIGN KEY(StateID) REFERENCES states(StateID) ON DELETE CASCADE
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

CREATE TABLE states(
    StateID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(14) NOT NULL UNIQUE,
    Abbreviation VARCHAR(2) NOT NULL UNIQUE
);

INSERT INTO states(Name, Abbreviation)
VALUES("Alabama", "AL"),
("Alaska", "AK"),
("Arizona", "AZ"),
("Arkansas", "AR"),
("California", "CA"),
("Colorado", "CO"),
("Connecticut", "CT"),
("Delaware", "DE"),
("Florida", "FL"),
("Georgia", "GA"),
("Hawaii", "HI"),
("Idaho", "ID"),
("Illinois", "IL"),
("Indiana", "IN"),
("Iowa", "IA"),
("Kansas", "KS"),
("Kentucky", "KY"),
("Louisiana", "LA"),
("Maine", "ME"),
("Maryland", "MD"),
("Massachusetts", "MA"),
("Michigan", "MI"),
("Minnesota", "MN"),
("Mississippi", "MS")
("Missouri", "MO"),
("Montana", "MT"),
("Nebraska", "NE"),
("Nevada", "NV"),
("New Hampshire", "NH"),
("New Jersey", "NJ"),
("New Mexico", "NM"),
("New York", "NY"),
("North Carolina", "NC"),
("North Dakota", "ND"),
("Ohio", "OH"),
("Oklahoma", "OK"),
("Oregon", "OR"),
("Pennsylvania", "PA"),
("Rhode Island", "RI"),
("South Carolina", "SC"),
("South Dakota", "SD"),
("Tennessee", "TN"),
("Texas", "TX"),
("Utah", "UT"),
("Vermont", "VT"),
("Virginia", "VA"),
("Washington", "WA"),
("West Virginia", "WV"),
("Wisconsin", "WI"),
("Wyoming", "WY");