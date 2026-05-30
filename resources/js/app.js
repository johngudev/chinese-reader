import './bootstrap';
import Alpine from 'alpinejs';
import { pinyin } from 'pinyin-pro';

window.Alpine = Alpine;
Alpine.start();

function consolePinyinOfText(text) {
    for (const char of text) {
        if (/\p{Script=Han}/u.test(char)) {
            console.log(char, '→', pinyin(char));
        }
    }
}

function wrapTextInPinyin(text) {
    let resultText = '';

    for (const char of text) {
        if (/\p{Script=Han}/u.test(char)) {
            resultText += `<span class='hanzi' data-pinyin='${pinyin(char)}'>${char}</span>`;
        } else {
            resultText += char;
        }
    }

    return resultText;
}

window.consolePinyinOfText = consolePinyinOfText;
window.wrapTextInPinyin = wrapTextInPinyin;