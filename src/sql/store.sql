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
    Activated BOOLEAN NOT NULL,
    Token VARCHAR(255),
    RoleID INT NOT NULL,
    FOREIGN KEY(RoleID) REFERENCES roles(RoleID)
);

CREATE TABLE collections(
    CollectionID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL
);

INSERT INTO collections(Name, Image) VALUES("Anime", "Anime.png"), ("Cartoons", "Cartoons.png"), ("Comics", "Comics.png"), ("Illustrations", "Illustrations.png"), ("Video Games", "VideoGames.png");

CREATE TABLE products(
    ProductID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Image VARCHAR(255) NOT NULL,
    CollectionID INT NOT NULL,
    FOREIGN KEY(CollectionID) REFERENCES collections(CollectionID) ON DELETE CASCADE
);

CREATE TABLE options(
    OptionID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Choices JSON
);

CREATE TABLE formats(
    FormatID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    OptionID INT,
    FOREIGN KEY(OptionID) REFERENCES options(OptionID)
);

INSERT INTO formats(Name) VALUES("Sticker"), ("T-Shirt");

CREATE TABLE items(
    ItemID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ProductID INT NOT NULL,
    FormatID INT NOT NULL,
    Price DECIMAL(6,2) NOT NULL,
    FOREIGN KEY(ProductID) REFERENCES products(ProductID) ON DELETE CASCADE,
    FOREIGN KEY(FormatID) REFERENCES formats(FormatID) ON DELETE CASCADE
);

CREATE TABLE addresses(
    AddressID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    Address VARCHAR(255) NOT NULL,
    City VARCHAR(255) NOT NULL,
    State VARCHAR(2) NOT NULL,
    Zipcode VARCHAR(5) NOT NULL,
    UserID INT,
    FOREIGN KEY(UserID) REFERENCES users(UserID) ON DELETE CASCADE
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
    AddressID INT,
    Total DECIMAL(6,2) NOT NULL,
    FOREIGN KEY(UserID) REFERENCES users(UserID),
    FOREIGN KEY(StatusID) REFERENCES statuses(StatusID),
    FOREIGN KEY(AddressID) REFERENCES addresses(AddressID)
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