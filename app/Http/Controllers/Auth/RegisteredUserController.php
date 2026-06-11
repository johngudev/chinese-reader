<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Initialize a non-empty characters list for the new user
        $characters = ['爱', '八', '爸', '杯', '子', '北', '京', '本', '不', '客', '气', '菜', '茶', '吃', '出', '租', '车', '打', '电', '话', '大', '的', '点', '脑', '视', '影', '东', '西', '都', '读', '对', '起', '多', '少', '儿', '二', '饭', '馆', '飞', '机', '分', '钟', '高', '兴', '个', '工', '作', '狗', '汉', '语', '好', '喝', '和', '很', '后', '面', '回', '会', '火', '站', '几', '家', '叫', '今', '天', '九', '开', '看', '见', '块', '来', '老', '师', '了', '冷', '里', '零', '六', '妈', '吗', '买', '猫', '没', '关', '系', '米', '名', '字', '明', '哪', '那', '呢', '能', '你', '年', '女', '朋', '友', '漂', '亮', '苹', '果', '七', '前', '钱', '请', '去', '热', '人', '认', '识', '日', '三', '商', '店', '上', '午', '什', '么', '十', '时', '候', '是', '书', '谁', '水', '睡', '觉', '说', '四', '岁', '他', '她', '太', '听', '同', '学', '喂', '我', '们', '五', '喜', '欢', '下', '雨', '先', '生', '现', '在', '想', '小', '姐', '些', '写', '谢', '星', '期', '习', '校', '一', '衣', '服', '医', '院', '椅', '有', '月', '再', '怎', '样', '这', '中', '国', '住', '桌', '昨', '坐', '做'];
        
        $user->charactersList()->updateOrCreate(
            [],                                  // empty = this user's existing row, if any
            ['characters_list' => $characters]
        );

        return redirect(RouteServiceProvider::HOME);
    }
}
