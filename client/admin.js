var current_editing_topic_id = null;

function open_add_topic() {
    //页面层
    layer.open({
        content: $("#add_topic_box").html()
        ,title: [
            '新话题',
            'background-color: #FF4351; color:#fff;'
        ]
    });
}

function add_topic() {
    var new_topic = $($(".new_topic")[1]).val();
    $.ajax({
        url:"admin.php",
        type:'POST',
        dataType: 'html',
        data:{api: 'add_topic',topic:new_topic},
        success: function () {
            location.reload();
        }
    });
}

function delete_topic(id) {
//询问框
    layer.open({
        content: '你确定要删除这个话题吗？'
        ,btn: ['确定', '不要']
        ,yes: function(index){
            $.ajax({
               url:"admin.php",
               type:'POST',
               dataType: 'html',
               data:{api: 'delete_topic',id:id},
               success: function () {
                    location.reload();
                }
            });
        }
    });
}

function open_edit_topic(id, content) {
    current_editing_topic_id = id;
    $(".update_topic").html(content);
    //页面层
    layer.open({
        content: $("#update_topic_box").html()
        ,title: [
            '修改话题',
            'background-color: #FF4351; color:#fff;'
        ]
    });
}
function update_topic() {
    var new_topic = $($(".update_topic")[1]).val();
    $.ajax({
        url:"admin.php",
        type:'POST',
        dataType: 'html',
        data:{api: 'update_topic',id:current_editing_topic_id, topic:new_topic},
        success: function () {
            location.reload();
        }
    });
}

// 定时获取服务器状态
function get_status() {
    $.ajax({
        url:"admin.php",
        type:'POST',
        dataType: 'json',
        data:{api: 'get_status'},
        success: function (result) {
            $("#room").text(result.rooms);
            $("#matching_boy").text(result.boy_matching);
            $("#matching_girl").text(result.girl_matching);
            $("#total_matching").text(result.total_matching);
            $("#total_boy").text(result.total_boy);
            $("#total_girl").text(result.total_girl);
            $("#total_chat").text(result.total_chat);
            $("#total_room").text(result.total_room);
            
        }
    });
}

window.setInterval("get_status()", 1000);


// 删除情头
function delete_headshot(id) {
    layer.open({
        content: '你确定要删除这组头像吗？'
        ,btn: ['确定', '不要']
        ,yes: function(index){
            $.ajax({
                url:"admin.php",
                type:'POST',
                dataType: 'html',
                data:{api: 'delete_headshot', id:id},
                success: function () {
                    location.reload();
                }
            });
        }
    });

}