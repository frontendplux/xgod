create table if not exist member(
    id  int auto_increment primary key,
    _id varchar(300) not null,
    user varchar(100) not null,
    email varchar(200) not null,
    profile json,
    pass varchar(300) not null,
    timeReg datetime,
    status enum('pending','active') default 'pending'
);