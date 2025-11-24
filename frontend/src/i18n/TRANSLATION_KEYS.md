# Translation Keys Reference

This document lists all translation keys and their locations in the i18n files.

## All Requested Keywords - Translation Key Mapping

| Keyword | Translation Key | English | Arabic | Kurdish |
|---------|----------------|---------|--------|---------|
| **My Friends** | `friends.title` | My Friends | أصدقائي | هاوڕێکانم |
| **Your friends list - click to chat with them** | `friends.subtitle` | Your friends list - click to chat with them | قائمة أصدقائك - انقر للدردشة معهم | لیستی هاوڕێکانت - کلیک بکە بۆ گفتوگۆ لەگەڵیان |
| **← Back to Conversations** | `friends.backToConversations` | ← Back to Conversations | ← رجوع إلى المحادثات | ← گەڕانەوە بۆ گفتوگۆکان |
| **Community** | `community.title` | Community | المجتمع | کۆمەڵگا |
| **My Profile** | `profile.title` | My Profile | ملفي الشخصي | پرۆفایلم |
| **Start Chat** | `friends.startChat` | Start Chat | بدء الدردشة | دەستپێکردنی گفتوگۆ |
| **Offline** | `friends.offline` / `chat.offline` | Offline | غير متصل | دەرهێڵ |
| **Conversation** | `chat.conversation` | Conversation | محادثة | گفتوگۆ |
| **Back** | `chat.back` | Back | رجوع | گەڕانەوە |
| **Send** | `chat.send` | Send | إرسال | ناردن |
| **Type your message** | `chat.typeMessage` | Type your message... | اكتب رسالتك... | پەیامەکەت بنووسە... |
| **Community Chat** | `community.chat.title` | Community Chat | دردشة المجتمع | گفتوگۆی کۆمەڵگا |
| **Chat with the community** | `community.chat.chatWithCommunity` | Chat with the community | تحدث مع المجتمع | گفتوگۆ لەگەڵ کۆمەڵگا |
| **Friend Requests** | `community.friendRequests.title` | Friend Requests | طلبات الصداقة | داواکاری هاوڕێیی |
| **Community Users** | `community.communityUsers` | Community Users | مستخدمو المجتمع | بەکارهێنەرانی کۆمەڵگا |
| **Add Friend** | `community.addFriend` | Add Friend | إضافة صديق | زیادکردنی هاوڕێ |
| **Chat** | `profile.chat` | Chat | دردشة | گفتوگۆ |
| **Remove** | `community.remove` | Remove | إزالة | لابردن |
| **Profile Information** | `profile.profileInformation` | Profile Information | معلومات الملف الشخصي | زانیاری پرۆفایل |
| **Edit Profile** | `profile.editProfile` | Edit Profile | تعديل الملف الشخصي | دەستکاریکردنی پرۆفایل |
| **Admin Dashboard** | `admin.title` | Admin Dashboard | لوحة تحكم المشرف | داشبۆردی بەڕێوەبەر |
| **Manage users and community chat** | `admin.manageUsers` | Manage users and community chat | إدارة المستخدمين ودردشة المجتمع | بەڕێوەبردنی بەکارهێنەران و گفتوگۆی کۆمەڵگا |
| **Total Users** | `admin.stats.totalUsers` | Total Users | إجمالي المستخدمين | کۆی بەکارهێنەران |
| **Conversations** | `admin.stats.totalConversations` | Conversations | المحادثات | گفتوگۆکان |
| **All Users** | `admin.users.title` | All Users | جميع المستخدمين | هەموو بەکارهێنەران |
| **Total Messages** | `admin.stats.totalMessages` | Total Messages | إجمالي الرسائل | کۆی پەیامەکان |
| **Delete** | `admin.users.delete` / `common.delete` | Delete | حذف | سڕینەوە |
| **Delete Messages** | `community.chat.deleteMessages` | Delete Messages | حذف الرسائل | سڕینەوەی پەیامەکان |

## Usage in Components

To use these translations in Vue components:

```vue
<template>
  <!-- Direct usage -->
  <h1>{{ $t('friends.title') }}</h1>
  
  <!-- With parameters -->
  <p>{{ $t('chat.typing', { name: userName }) }}</p>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// In script
const message = t('chat.send')
</script>
```

## Language Files

- `frontend/src/i18n/locales/en.json` - English translations
- `frontend/src/i18n/locales/ar.json` - Arabic translations (RTL)
- `frontend/src/i18n/locales/ku.json` - Kurdish translations (RTL)

