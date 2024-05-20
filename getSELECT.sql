--渲染出用戶擁有的page

SELECT * FROM `type_page` WHERE user_id = 1

--修改已有的page資料

UPDATE type_page 
SET page_name = 'PHP_Coding'
WHERE Type_page_id = 2

--篩選出目標頁的SQL指令

SELECT word.user_id,word,page_name
FROM word_page_bridge
INNER JOIN word ON word_page_bridge.word_id = word.word_id
INNER JOIN type_page ON word_page_bridge.Type_page_id = type_page.Type_page_id
WHERE type_page.Type_page_id = 1
;


--群組生成同時生成和頁面的bridge

INSERT INTO word_group(Type_page_id,group_name,order)
VALUES (1,'英文的群組A');
SET @lastID = LAST_INSERT_ID(); -- 获取自动生成的 ID
-- 使用获取的 ID，在 bridge_table 插入新记录
INSERT INTO word_page_bridge (word_id, Type_page_id) 
VALUES (@last_id_in_A_table, :Page);


--刪除和新增字和群組的關聯
WITH Del_WGB AS (
    SELECT wgb.word_id, wgb.group_id
    FROM word_group_bridge wgb
    LEFT JOIN word_group g ON wgb.group_id = g.group_id
    WHERE g.Type_page_id = 2 AND g.user_id = 1 AND wgb.word_id = 1
)

SELECT * FROM word_group_bridge
WHERE (word_id, group_id) IN (
    SELECT word_id, group_id FROM Del_WGB
);
