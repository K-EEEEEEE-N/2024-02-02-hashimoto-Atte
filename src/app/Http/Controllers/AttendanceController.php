<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use Illuminate\Pagination\Paginator;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // ユーザーの勤務情報を取得
        $attendanceData = Attendance::where('user_id', $user->id)
            ->whereNotNull('begin') // 勤務開始ボタンが押されたデータのみを対象に
            ->orderByDesc('begin') // 最新のものを取得するために begin カラムを降順に並べ替え
            ->first();

        $attendanceDataNew = Attendance::Where('user_id', $user->id)
            ->whereNotNull('break_begin') // 休憩開始ボタンが押されたデータのみを対象に
            ->orderByDesc('break_begin') // 最新のものを取得するために break_begin カラムを降順に並べ替え
            ->first();

        // ビューに渡すデータ
        $data = [
            'search_item' => [
                'begin' => optional($attendanceData)->begin,
                'finish' => optional($attendanceData)->finish,
                'break_begin' => optional($attendanceDataNew)->break_begin,
                'break_finish' => optional($attendanceDataNew)->break_finish,
            ],
        ];

        return view('stamping', $data);
    }

    public function attendance(Request $request)
    {
        $user = Auth::user();

        // 選択された日付があればそれを使い、なければ今日の日付を取得
        $selectedDate = $request->input('selectedDate', now()->toDateString());

        // ユーザーの勤務情報をページネーションで取得
        $attendances = Attendance::whereDate('begin', $selectedDate)
            ->orderByDesc('begin') // あるいは 'created_at' など適切なカラムを選択
            ->paginate(5);

        // ビューに渡すデータ
        $data = [
            'attendances' => $attendances,
            'currentDate' => $selectedDate, // 追加: 選択された日付をビューに渡す
        ];

        // 各勤怠レコードごとに総休憩時間を計算
        foreach ($attendances as $attendance) {
            $attendance->total_break_time = $this->calculateTotalBreakTime($attendance);
        }

        return view('attendance', $data);
    }

    private function calculateTotalBreakTime($attendance)
    {
        // attendanceテーブルから休憩開始時間と休憩終了時間を取得
        $breakBegin = $attendance->break_begin;
        $breakFinish = $attendance->break_finish;

        // 休憩時間が有効な範囲内のみ計算
        if ($breakBegin && $breakFinish) {
            $breakStart = Carbon::parse($breakBegin);
            $breakEnd = Carbon::parse($breakFinish);

            // 休憩時間の合計を秒単位で計算
            $totalBreakTimeSeconds = $breakEnd->diffInSeconds($breakStart);

            // 秒単位の休憩時間を 'H:i:s' 形式に変換
            $totalBreakTimeFormatted = gmdate('H:i:s', $totalBreakTimeSeconds);

            // 合計時間を '00:00:00' 形式に変換して返す

            return $totalBreakTimeFormatted;
        }

        // 休憩開始時間または休憩終了時間がない場合はゼロを返す
        return '00:00:00';
    }

    public function register(RegisterRequest $request)
    {
        // バリデーションルール
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // パスワードと確認用パスワードが一致しない場合のエラーメッセージをカスタマイズ
        $validator->sometimes('password_confirmation', 'same:password', function ($input) {
            return !empty($input->password);
        });

        // バリデーションエラーがある場合
        if ($validator->fails()) {
            return redirect('/register')
                ->withErrors($validator)
                ->withInput();
        }

        // ユーザー登録
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // 登録成功時の処理（例: ログイン）
        Auth::login($user);

        // リダイレクト先などを適切に設定
        return redirect('/login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            // 認証成功時の処理
            return redirect('/');
        } else {
            // 認証失敗時の処理
            return back()->withErrors(['email' => 'メールアドレスかパスワードが正しくありません。']);
        }
    }

    public function beginWork()
    {
        $user = Auth::user();

        // 今日の日付を取得
        $today = now()->toDateString();

        // ユーザーの過去の勤務開始データを取得（最新のデータを取得するために begin カラムを降順に並べ替え）
        $latestBeginData = Attendance::where('user_id', $user->id)
            ->whereDate('begin', $today)
            ->orderByDesc('begin')
            ->first();

        // 過去に勤務開始ボタンが押されている場合は新しいレコードに保存
        if ($latestBeginData && is_null($latestBeginData->finish)) {
            // 今日の日付に既に勤務が開始されている場合
            return redirect('/');
        }

        // ユーザーの勤務開始時間を保存
        Attendance::create([
            'user_id' => $user->id,
            'begin' => now(),
        ]);

        return redirect('/');
    }

    public function endWork()
    {
        $user = Auth::user();

        // 今日の日付を取得
        $today = now()->toDateString();

        // ユーザーの過去の勤務開始データを取得
        $pastBeginData = Attendance::where('user_id', $user->id)
            ->whereDate('finish', $today)
            ->first();

        // ユーザーの勤務終了時間を保存
        Attendance::where('user_id', $user->id)
            ->whereNull('finish')
            ->whereNotNull('begin') // 勤務開始ボタンが押されているレコードの条件を追加
            ->update(['finish' => now()]);

        return redirect('/');
    }

    public function beginBreak()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // 既に休憩開始ボタンが押されているか確認
        $existingBreak = Attendance::where('user_id', $user->id)
            ->whereDate('begin', $today)
            ->whereNotNull('break_begin')
            ->whereNull('break_finish')
            ->first();

        if ($existingBreak) {
            // 既に休憩開始ボタンが押されています。
            return redirect('/');
        }

        // 直前に押された勤務開始ボタンのIDを取得
        $lastWorkBegin = optional($this->getLatestEvent('begin'));

        if ($lastWorkBegin && is_null($lastWorkBegin->finish)) {
            // 休憩開始ボタンが押されており、かつ直前の勤務開始ボタンがまだ終了していない場合
            // 直前の勤務開始ボタンと同じレコードに休憩開始時間を保存
            $lastWorkBegin->update(['break_begin' => now()]);
        } else {
            // 休憩開始ボタンが押されていないか、直前の勤務開始ボタンが既に終了している場合はエラーメッセージ
            return redirect('/');
        }

        return redirect('/');
    }

    public function endBreak()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // 直前に押された休憩開始ボタンのIDを取得
        $lastBreakBegin = optional($this->getLatestEvent('break_begin'));

        if ($lastBreakBegin && is_null($lastBreakBegin->break_finish)) {
            // 休憩終了ボタンが押されていない場合は更新
            $lastBreakBegin->update(['break_finish' => now()]);

            // 休憩時間の合計を再計算して break_total カラムに加算
            $this->updateBreakTotal($user->id,$today, $lastBreakBegin);

            // 休憩開始時間と終了時間を消去
            $lastBreakBegin->update(['break_begin' => null, 'break_finish' => null]);

            return redirect('/');
        }
    }

    private function updateBreakTotal($userId, $date, $attendance)
    {
        // 指定日の勤務レコードを取得
        // $attendance を使っているため、latest() は不要
        // $attendance には最新の休憩終了ボタンが押された勤務レコードが渡されています
        // $attendance は既に最新のものであるため再度ソートする必要はありません

        if ($attendance) {
            // 休憩時間の合計を計算
            $totalBreakTime = $this->calculateTotalBreakTime($attendance);

            // 'HH:MM:SS' 形式にフォーマット
            $formattedTotalBreakTime = Carbon::createFromFormat('H:i:s', $totalBreakTime)->toTimeString();

            // break_total カラムに合計を保存
            $attendance->update([
                'break_total' => $formattedTotalBreakTime,
                'break_begin' => null,
                'break_finish' => null,
            ]);
        }
    }

    private function getLatestEvent($eventType)
    {
        $user = Auth::user();

        return Attendance::where('user_id', $user->id)
            ->whereNotNull($eventType)
            ->latest()
            ->first();
    }

    public function users()
    {
        // ユーザーがログインしているか確認
        if (!Auth::check()) {
            // ログインしていない場合はログインページにリダイレクト
            return redirect('/login');
        }
        // 'users' テーブルから全てのユーザーを取得
        $users = User::all();

        // ユーザーデータをビューに渡す
        $data = [
            'users' => $users,
        ];

        // ユーザーデータを含んだビューを返す
        return view('users', $data);
    }
}
