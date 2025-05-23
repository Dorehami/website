import {driver} from "driver.js";
import "driver.js/dist/driver.css";

export const localstorageKey = 'intro'

const driverObj = driver({
    showProgress: false,
    popoverClass: 'custom-popover',
    smoothScroll: true,
    animate: true,
    showButtons: ["next", "close"],
    doneBtnText: 'متوجه شدم',
    nextBtnText: 'بعدی',
    prevBtnText: 'قبلی',
    progressText: '{{current}} از {{total}}',
    steps: [
        {
            element: '#intro-upvote',
            popover: {
                title: 'لایک یا رای مثبت',
                description: 'از طریق این علامت، میتونید به پست رای مثبت و امتیازش رو افزایش بدید.',
                side: "right",
                align: 'end',
            },
        },
        {
            element: '#intro-feedback',
            popover: {
                title: 'فیدبک به جامعه',
                description: 'از این لینک میتونید وارد صفحه‌ی بازخوردها بشید و پیشنهاداتتون رو به جامعه دورهمی ارائه بدید',
                side: "right",
                align: 'end',
            },
        },
    ],
    onDestroyStarted: (element, step, options) => {
        localStorage.setItem(localstorageKey, true)
        driverObj.destroy();
    }
});

const hasSeenTheTips = localStorage.getItem(localstorageKey) === 'true';

if (!hasSeenTheTips) {
    driverObj.drive();
}