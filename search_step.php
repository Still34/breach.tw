<?php
$title = '搜尋洩漏紀錄';
require 'src/common.php';
require 'src/main.head.php';
?>

<body>
    <?php require 'src/main.banner.php' ?>

    <div class="container grid-xl content">
        <section class="card" id="generator-card">
            <div class="card-header">
                <h3 class="card-title">雜湊產生器</h3>
                <div class="card-subtitle">此方法可避免將個人資料直接回傳給我們。<br>產生後請貼上到下方表單進行追蹤或搜尋，雜湊值產生方法為：<code>sha1(姓名+身分證)</code></div>
            </div>
            <div class="card-body">
                <div class="ts negative message" id="missingkeyword1" style="display: none;">
                    <div class="header">缺少姓名</div>
                    <p>請輸入姓名</p>
                </div>
                <div class="ts negative message" id="missingkeyword2" style="display: none;">
                    <div class="header">缺少身分證字號</div>
                    <p>請輸入身分證字號</p>
                </div>
                <div class="ts positive message" id="hash_res" style="display: none;">
                    <div class="header">雜湊代碼</div>
                    <p id="real_res"></p>
                </div>

                <form class="form form-group" onsubmit="gen_sha1(this); return false;">
                    <div class="container">
                        <div class="columns">
                            <div class="field column">
                                <label class="form-label">姓名<span style="color:red;">*</span></label>
                                <input class="form-input" id="fullname" />
                            </div>
                            <div class="field column">
                                <label class="form-label">身分證字號<span style="color:red;">*</span></label>
                                <input class="form-input" id="nid" maxlength="10" />
                            </div>
                        </div>
                        <div class="column p-2">
                            <button class="btn" type="submit">產生</button>
                        </div>
                    </div>
                </form>
            </div>
            <script>
                function gen_sha1(form) {
                    $('#hash_res').hide();
                    $('#missingkeyword1').hide();
                    $('#missingkeyword2').hide();
                    if (form.fullname.value == '') {
                        $('#missingkeyword1').show();
                    } else if (form.nid.value == '') {
                        $('#missingkeyword2').show();
                    } else {
                        $('#real_res').text(sha1(form.fullname.value + form.nid.value.toUpperCase()));
                        $('#hash_res').show();
                    }
                }
            </script>
        </section>
        <section class="card" id="search-card">
            <div class="card-header">
                <h3 class="card-title">搜尋表單</h3>
                <div class="card-subtitle">產生後請貼上到下方表單進行追蹤或搜尋</div>
            </div>
            <div class="card-body">
                <div class="ts negative message" id="missingkeyword3" style="display: none;">
                    <div class="header">缺少雜湊值</div>
                    <p>請輸入雜湊代碼</p>
                </div>
                <div class="ts negative message" id="missingkeyword4" style="display: none;">
                    <div class="header">輸入錯誤</div>
                    <p>雜湊代碼格式錯誤</p>
                </div>
                <div class="ts negative message" id="backenderr" style="display: none;">
                    <div class="header">後端錯誤</div>
                    <p id="backenderr_text"></p>
                </div>
                <div class="ts negative message" id="breach" style="display: none;">
                    <div class="header">真是太糟糕了</div>
                    <p>發現個資洩漏情形<br>
                        已發現項目：<ul id="breach_list"></ul>
                    </p>
                    <p><button class="ts button secondary right" onclick="window.location='subscribe.php';">訂閱外洩事件</button></p>
                </div>
                <div class="ts positive message" id="nobreach" style="display: none;">
                    <div class="header">搜索完畢</div>
                    <p>您的個資目前未在大規模洩漏中找到，不過可能只是未被本網站發現<br>再接再厲，繼續保持。</p>
                    <p><button class="ts button secondary right" onclick="window.location='subscribe.php';">訂閱外洩事件</button></p>
                </div>
                <form class="form form-group" onsubmit="search_func(this); return false;">
                    <div class="container">
                        <div class="columns">
                            <div class="field column">
                                <label class="form-label">雜湊代碼<span style="color:red;">*</span></label>
                                <input class="form-input" id="hash" maxlength="40" />
                            </div>
                        </div>
                        <div class="column p-2">
                            <button class="btn" type="submit" id="search">搜尋</button>
                        </div>
                    </div>
                </form>
                <script src="https://www.google.com/recaptcha/api.js?render=<?= RECAPTCHA_SITE_KEY ?>"></script>
                <script>
                    Object.size = function(obj) {
                        var size = 0,
                            key;
                        for (key in obj) {
                            if (obj.hasOwnProperty(key)) size++;
                        }
                        return size;
                    };

                    function search_func(form) {
                        $('#nobreach').hide();
                        $('#breach').hide();
                        $('#missingkeyword3').hide();
                        $('#missingkeyword4').hide();
                        $('#backenderr').hide();
                        $('#search').attr('disabled', true);
                        grecaptcha.execute('<?= RECAPTCHA_SITE_KEY ?>', {
                            action: 'search'
                        }).then(function(token) {
                            $.getJSON('/api/search.php?hash=' + form.hash.value + '&token=' + token, function(res) {
                                $('#search').attr('disabled', false);
                                if (res.status == 0) {
                                    if (Object.size(res.result) > 0) {
                                        $('#breach_list')[0].innerHTML = '';
                                        for (source in res.result) {
                                            $('#breach_list').append('<li>' + source + '：' + res.result[source].join('、') + '</li>');
                                        }
                                        $('#breach').show();
                                    } else {
                                        $('#nobreach').show();
                                    }
                                } else {
                                    $('#backenderr_text')[0].innerText = res.error;
                                    $('#backenderr').show();
                                }
                            });
                        });
                    }
                </script>
            </div>
        </section>
    </div>
    <?php require 'src/main.footer.php'; ?>
</body>