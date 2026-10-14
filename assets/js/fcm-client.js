/**
 * EIMBox Modern Console Push Notification Client Module
 * File: /assets/js/fcm-client.js
 * Platform: web_modern (https://console.eimbox.com)
 */

(function () {
  'use strict';

  const FIREBASE_CONFIG = {
    apiKey: "AIzaSyDPJwwgH_kOtdyM1cIH_f1rn0r02iQPyFw",
    authDomain: "eimbox-9014d.firebaseapp.com",
    databaseURL: "https://eimbox-9014d-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId: "eimbox-9014d",
    storageBucket: "eimbox-9014d.firebasestorage.app",
    messagingSenderId: "534346978082",
    appId: "1:534346978082:web:7176ded7b904771241cb7b",
    measurementId: "G-KZM4CLKYES"
  };

  const VAPID_KEY = "BNZoLP2tUNODWUhPNvfpbp8WvVqSJQAkb76ccpIuPCVn6Uz4a6MtnbW4BOn8UtP-VSuAD-oY-gjjPq-hxLzlehw";
  const PLATFORM_TAG = "web_modern";

  // Dynamically resolve base URL (e.g. '/' on production or '/eimbox-dashboard/eimbox-materio/' on localhost)
  function resolveBasePath() {
    try {
      const script = document.currentScript || document.querySelector('script[src*="fcm-client.js"]');
      if (script && script.src) {
        const url = new URL(script.src);
        let base = new URL('../../', url).pathname;
        if (!base.endsWith('/')) base += '/';
        return base;
      }
    } catch (e) {}
    const match = window.location.pathname.match(/^(\/.*?\/(?:eimbox-panel|eimbox-materio|EIMBox-Play)\/)/i);
    if (match) return match[1];
    return '/';
  }

  const BASE_PATH = resolveBasePath();
  const API_ENDPOINT = BASE_PATH + "api/register_device.php";
  const SW_URL = BASE_PATH + "firebase-messaging-sw.js";

  class EIMBoxPushClient {
    constructor() {
      this.messaging = null;
      this.swRegistration = null;
      this.currentToken = null;
    }

    async init() {
      if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        console.warn('[EIMBox Push Modern] Web Push is not supported in this browser or context.');
        return;
      }

      try {
        await this.loadFirebaseScripts();

        if (!firebase.apps.length) {
          firebase.initializeApp(FIREBASE_CONFIG);
        }
        this.messaging = firebase.messaging();

        this.swRegistration = await navigator.serviceWorker.register(SW_URL, { scope: BASE_PATH });
        console.log('[EIMBox Push Modern] Service Worker registered with scope:', this.swRegistration.scope);

        if (Notification.permission === 'granted') {
          await this.syncToken();
        } else if (Notification.permission === 'default') {
          this.setupPermissionPrompt();
        }

        this.messaging.onMessage((payload) => {
          console.log('[EIMBox Push Modern] Foreground message received:', payload);
          this.showInAppNotification(payload);
        });

      } catch (err) {
        console.error('[EIMBox Push Modern] Initialization error:', err);
      }
    }

    loadFirebaseScripts() {
      return new Promise((resolve, reject) => {
        if (window.firebase && window.firebase.messaging) {
          resolve();
          return;
        }

        const script1 = document.createElement('script');
        script1.src = 'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js';
        script1.onload = () => {
          const script2 = document.createElement('script');
          script2.src = 'https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js';
          script2.onload = resolve;
          script2.onerror = reject;
          document.head.appendChild(script2);
        };
        script1.onerror = reject;
        document.head.appendChild(script1);
      });
    }

    async requestPermissionAndRegister() {
      try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
          console.log('[EIMBox Push Modern] Notification permission granted.');
          await this.syncToken();
          return true;
        } else {
          console.warn('[EIMBox Push Modern] Notification permission denied/dismissed.');
          return false;
        }
      } catch (err) {
        console.error('[EIMBox Push Modern] Permission request error:', err);
        return false;
      }
    }

    async syncToken() {
      try {
        const token = await this.messaging.getToken({
          vapidKey: VAPID_KEY,
          serviceWorkerRegistration: this.swRegistration
        });

        if (token) {
          this.currentToken = token;
          console.log('[EIMBox Push Modern] Fetched FCM Token successfully.');
          await this.sendTokenToServer(token);
        }
      } catch (err) {
        console.error('[EIMBox Push Modern] Failed to retrieve FCM token:', err);
      }
    }

    getOrCreateDeviceUuid() {
      let uuid = localStorage.getItem('eimbox_modern_device_uuid');
      if (!uuid) {
        uuid = 'dev_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36);
        localStorage.setItem('eimbox_modern_device_uuid', uuid);
      }
      return uuid;
    }

    detectBrowser() {
      const ua = navigator.userAgent;
      if (ua.includes('Edg/')) return 'Edge';
      if (ua.includes('Chrome/')) return 'Chrome';
      if (ua.includes('Firefox/')) return 'Firefox';
      if (ua.includes('Safari/')) return 'Safari';
      return 'Browser';
    }

    async sendTokenToServer(token) {
      const deviceUuid = this.getOrCreateDeviceUuid();
      const payload = {
        action: 'register',
        device_id: deviceUuid,
        device_uuid: deviceUuid,
        fcm_token: token,
        platform: PLATFORM_TAG,
        browser_name: this.detectBrowser(),
        browser_version: navigator.userAgent,
        os_name: navigator.platform,
        device_type: /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop',
        endpoint_origin: window.location.origin
      };

      try {
        const res = await fetch(API_ENDPOINT, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const json = await res.json();
        console.log('[EIMBox Push Modern] Server registration response:', json);
      } catch (err) {
        console.error('[EIMBox Push Modern] Failed to send token to server:', err);
      }
    }

    async deactivateOnLogout() {
      const deviceUuid = localStorage.getItem('eimbox_modern_device_uuid');
      if (!this.currentToken && !deviceUuid) return;

      try {
        await fetch(API_ENDPOINT, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'logout',
            device_uuid: deviceUuid,
            fcm_token: this.currentToken
          })
        });
        if (this.messaging) {
          await this.messaging.deleteToken();
        }
        console.log('[EIMBox Push Modern] Device deactivated on logout.');
      } catch (e) {
        console.error('[EIMBox Push Modern] Logout deactivation error:', e);
      }
    }

    setupPermissionPrompt() {
      // Check if user previously clicked 'Later' (snoozed for 3 days)
      const snoozeUntil = parseInt(localStorage.getItem('eimbox_push_snooze_until') || '0', 10);
      if (Date.now() < snoozeUntil) {
        return;
      }

      setTimeout(() => {
        if (Notification.permission === 'default') {
          this.showPermissionBanner();
        }
      }, 3500);
    }

    showPermissionBanner() {
      if (document.getElementById('eimbox-push-prompt-banner')) return;

      const banner = document.createElement('div');
      banner.id = 'eimbox-push-prompt-banner';
      banner.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        max-width: 360px;
        background: #1e1e2d;
        color: #f1f5f9;
        border: 1px solid rgba(251, 191, 36, 0.4);
        box-shadow: 0 16px 36px rgba(0,0,0,0.65);
        border-radius: 12px;
        padding: 14px 16px;
        z-index: 99999;
        font-family: system-ui, -apple-system, sans-serif;
        display: flex;
        flex-direction: column;
        gap: 10px;
      `;

      banner.innerHTML = `
        <div style="display: flex; align-items: flex-start; gap: 10px;">
          <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(251,191,36,0.15); display: flex; align-items: center; justify-content: center; color: #fbbf24; font-size: 1.15rem; flex-shrink: 0;">
            <i class="ri ri-notification-3-line"></i>
          </div>
          <div style="flex: 1;">
            <div style="font-size: 0.88rem; font-weight: 500; color: #f8fafc; margin-bottom: 2px;">নোটিফিকেশন চালু করুন</div>
            <div style="font-size: 0.78rem; color: #94a3b8; line-height: 1.4;">তাৎক্ষণিক পরীক্ষার ফলাফল ও প্রাতিষ্ঠানিক আপডেট পেতে নোটিফিকেশন অন রাখুন।</div>
          </div>
        </div>
        <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 4px;">
          <button id="eimbox-push-btn-later" style="padding: 5px 12px; font-size: 0.76rem; border-radius: 6px; background: transparent; color: #94a3b8; border: 1px solid rgba(255,255,255,0.1); cursor: pointer;">পরে</button>
          <button id="eimbox-push-btn-allow" style="padding: 5px 14px; font-size: 0.76rem; border-radius: 6px; background: #fbbf24; color: #000; font-weight: 500; border: none; cursor: pointer;">অনুমতি দিন</button>
        </div>
      `;

      document.body.appendChild(banner);

      document.getElementById('eimbox-push-btn-later').addEventListener('click', () => {
        const snoozeUntilTime = Date.now() + (3 * 24 * 60 * 60 * 1000);
        localStorage.setItem('eimbox_push_snooze_until', snoozeUntilTime);
        banner.remove();
      });

      document.getElementById('eimbox-push-btn-allow').addEventListener('click', async () => {
        localStorage.removeItem('eimbox_push_snooze_until');
        banner.remove();
        await this.requestPermissionAndRegister();
      });
    }

    playNotificationChime() {
      try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        const now = ctx.currentTime;
        osc.frequency.setValueAtTime(587.33, now);
        osc.frequency.exponentialRampToValueAtTime(880.00, now + 0.12);
        gain.gain.setValueAtTime(0.25, now);
        gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now);
        osc.stop(now + 0.6);
      } catch (e) {}
    }

    showInAppNotification(payload) {
      this.playNotificationChime();

      const title = payload.notification?.title || payload.data?.title || 'বিজ্ঞপ্তি';
      const body = payload.notification?.body || payload.data?.body || payload.data?.message || '';
      const imageUrl = payload.notification?.image || payload.data?.image || payload.data?.image_url || null;
      const url = payload.fcmOptions?.link || payload.data?.click_action || payload.data?.url || payload.data?.link || '';
      const category = payload.data?.category || 'বিজ্ঞপ্তি';

      const existingOld = document.getElementById('eimbox-modern-notif-card');
      if (existingOld) existingOld.remove();

      const card = document.createElement('div');
      card.id = 'eimbox-modern-notif-card';
      card.style.cssText = `
        position: fixed;
        top: 24px;
        right: 24px;
        max-width: 420px;
        width: calc(100vw - 48px);
        background: #2b2c40;
        color: #f1f5f9;
        border: 1px solid rgba(115, 103, 240, 0.45);
        box-shadow: 0 16px 45px rgba(0,0,0,0.65);
        border-radius: 12px;
        padding: 16px;
        z-index: 999999;
        font-family: system-ui, sans-serif;
        box-sizing: border-box;
        animation: eimboxModernSlide 0.35s cubic-bezier(0.16, 1, 0.3, 1);
      `;

      let imgHtml = '';
      if (imageUrl) {
        imgHtml = `<div style="margin: 10px 0; border-radius: 8px; overflow: hidden; max-height: 160px; background: #1f2030; display: flex; align-items: center; justify-content: center;">
          <img src="${imageUrl}" alt="Banner" style="width: 100%; height: auto; max-height: 160px; object-fit: cover; display: block;" onerror="this.parentElement.style.display='none'">
        </div>`;
      }

      card.innerHTML = `
        <style>
          @keyframes eimboxModernSlide {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
          }
        </style>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
          <div style="display: flex; align-items: center; gap: 8px;">
            <div style="width: 28px; height: 28px; border-radius: 50%; background: rgba(115, 103, 240, 0.18); display: flex; align-items: center; justify-content: center; color: #7367f0; font-size: 0.95rem;">
              <i class="bi bi-bell-fill"></i>
            </div>
            <span style="font-size: 0.78rem; color: #7367f0; text-transform: uppercase; letter-spacing: 0.5px;">${category}</span>
          </div>
          <button id="eimbox-modern-close-btn" style="background: transparent; border: none; color: #a8aaae; font-size: 1.25rem; line-height: 1; cursor: pointer; padding: 2px 6px;">&times;</button>
        </div>
        <div style="font-size: 0.95rem; color: #ffffff; margin-bottom: 4px; line-height: 1.35;">${title}</div>
        <div style="font-size: 0.83rem; color: #b6bee3; line-height: 1.45; word-break: break-word;">${body}</div>
        ${imgHtml}
        <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 12px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px;">
          <button id="eimbox-modern-dismiss-btn" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; background: transparent; color: #a8aaae; border: 1px solid rgba(255,255,255,0.15); cursor: pointer;">পরে</button>
          ${url ? `<button id="eimbox-modern-view-btn" style="padding: 6px 18px; font-size: 0.8rem; border-radius: 6px; background: #7367f0; color: #fff; border: none; cursor: pointer;">বিস্তারিত দেখুন <i class="bi bi-arrow-right-short"></i></button>` : ''}
        </div>
      `;

      document.body.appendChild(card);

      document.getElementById('eimbox-modern-close-btn').addEventListener('click', () => card.remove());
      document.getElementById('eimbox-modern-dismiss-btn').addEventListener('click', () => card.remove());
      if (url) {
        document.getElementById('eimbox-modern-view-btn').addEventListener('click', () => {
          card.remove();
          window.location.href = url;
        });
      }

      setTimeout(() => {
        if (card.parentElement) card.remove();
      }, 12000);
    }
  }

  window.EIMBoxModernPush = new EIMBoxPushClient();

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => window.EIMBoxModernPush.init());
  } else {
    window.EIMBoxModernPush.init();
  }
})();
