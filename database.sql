CREATE USER 'NoteToolController'@'localhost' IDENTIFIED BY 'ToolMaker';


drop database if exists NoteTool;
create database NoteTool default character set utf8mb4 collate utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON NoteTool.* TO 'NoteToolController'@'localhost';

use NoteTool;
create table Login (
    account VARCHAR(20) NOT NULL PRIMARY key,
    password VARCHAR(30) NOT NULL 
);

create table UserData (
    account VARCHAR(20) NOT NULL UNIQUE, 
	user_id int auto_increment primary key, 
	user_name varchar(15) not null, 
	avatar_img varchar(120) not null,
    FOREIGN KEY (account) REFERENCES Login(account) ON DELETE CASCADE
);

create table Type_page (
    Type_page_id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    page_name varchar(50),
    page_content varchar(200),
    FOREIGN KEY (user_id) REFERENCES UserData(user_id) ON DELETE CASCADE
);

create table word (
	word_id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    word varchar(30),
    word_name varchar(30),
    word_content text,
    FOREIGN KEY (user_id) REFERENCES UserData(user_id) ON DELETE CASCADE
);

create table word_group (
    group_id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    Type_page_id int,
    `Order` int,
    group_name varchar(30),
    FOREIGN KEY (Type_page_id) REFERENCES Type_page(Type_page_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES UserData(user_id) ON DELETE CASCADE
);

create table word_page_bridge(
    word_id int,
    Type_page_id int,
    PRIMARY KEY (word_id, Type_page_id),
    FOREIGN KEY (word_id) REFERENCES word(word_id) ON DELETE CASCADE,
    FOREIGN KEY (Type_page_id) REFERENCES Type_page(Type_page_id) ON DELETE CASCADE
);

create table word_group_bridge(
    word_id int,
    group_id int,
    PRIMARY KEY (word_id, group_id),
    FOREIGN KEY (word_id) REFERENCES word(word_id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES word_group(group_id) ON DELETE CASCADE
);

insert into Login values('MusicWolf', 'WolfEdit');
insert into UserData values('MusicWolf', Null , '音狼' , 'https://wolf-test-box.s3.ap-northeast-1.amazonaws.com/HarlerArt/1110420瀞吾.png');
insert into Type_page values
    ( Null , 1 , '英文-English', '這裡是用來寫關於英文單字的'),
    ( Null , 1 , 'php-Coding', '這裡是用來寫關於PHP字卡的');

insert into word values
    ( Null, 1 , 'element','元素', '物件元素內容'),
    ( Null, 1 , 'version','版本', '版本內容？'),
    ( Null, 1 , 'content','內容', ''),
    ( Null, 1 , 'explode()','切割字串', '切掉字串'),
    ( Null, 1 , 'session','超全域函數', '可以儲存使用者資料');

insert into word_page_bridge values
    (1,1),(2,1),(3,1),(4,2),(5,2);





-- create table word_tag_bridge(
--     word_id int,
--     tag_id int,
--     PRIMARY KEY (word_id, tag_id),
--     FOREIGN KEY (word_id) REFERENCES word(word_id) ON DELETE CASCADE,
--     FOREIGN KEY (tag_id) REFERENCES tag(tag_id) ON DELETE CASCADE
-- );


-- create table tag (
--     tag_id int AUTO_INCREMENT PRIMARY KEY,
--     tag_name varchar(20)
-- );