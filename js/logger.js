document.addEventListener('DOMContentLoaded', function () {

    // URL解析
    const url = new URL(window.location.href);
    
    // パラメータ取得
    const rid = url.searchParams.get('rid');
    const search = url.searchParams.get('search');

    // 詳細検索フィールド (f_ で始まるパラメータ) をすべて取得
    const searchfields = [...url.searchParams.entries()]
        .filter(([key, val]) => key.startsWith('f_') && val)
        .map(([key, val]) => [key, val]);

    // コースIDの取得 (Moodleの設定オブジェクト、またはHTML属性から)
    const courseId = M.cfg.courseId || document.body.dataset.courseid || null;

    let action = null;
    let payload = {};

    // アクションの判定
    if (rid) {
        // --- 閲覧 (Viewed) ---
        action = 'viewed';
        payload = { 
            recordid: parseInt(rid), 
            searchquery: null 
        };
    } else if (search || searchfields.length > 0) {
        // --- 検索 (Searched) ---
        action = 'searched';
        
        // 検索クエリの組み立て
        const searchPairs = searchfields.map(([key, val]) => `${key}=${val}`);
        const searchquery = search ? `search=${search}` : searchPairs.join('&');

        payload = {
            recordid: null,
            searchquery: searchquery
        };
    }

    // データが揃っていれば送信
    if (action && courseId) {
        fetch(M.cfg.wwwroot + '/local/dbviewrecorder/logrecord.php?sesskey=' + M.cfg.sesskey, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: action,
                recordid: payload.recordid,
                searchquery: payload.searchquery,
                courseid: courseId
            })
        })
        .then(response => {
            // 成功時の処理が必要ならここに記述（通常はログなのでサイレントでOK）
        })
        .catch(error => {
            console.error('[dbviewrecorder] Log error:', error);
        });
    } else {
        // デバッグ用: 必要なデータが足りない場合
        // console.warn('[dbviewrecorder] Skipped logging due to missing data');
    }
});