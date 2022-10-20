PAYUNi 統一金流 OpenCart 4.0 模組
===============

提供使用 OpenCart Payment 模組時，可直接透過安裝設定此套件，以便於快速串接統一金流PAYUNi之金流系統。

目錄
-----------------
* [支援版本](#支援版本)
* [安裝](#安裝)
* [設定](#設定)

支援版本
-----------------
| OpenCart | PHP |
| :---------: | :---------: |
| 4.0.1.1 | 8.1.10 |

安裝
-----------------

#### 下載模組
請至[Release頁面](https://github.com/payuni/OpenCart4.0/releases)下載最新版本
`Release頁面` -> 最新版本的`Assets` -> 下載 `payunipayment.ocmod.zip`。

#### 上傳模組
`OpenCart後台` -> `擴充模組安裝(Extension Installer)`，上傳 `payunipayment.ocmod.zip` -> 已安裝列表出現統一金流模組 -> 點擊右方安裝按鈕。
![](https://raw.githubusercontent.com/payuni/sample_picture/main/opencart4/opencart4_add.png)

設定
-----------------

**啟用**
- `OpenCart後台` -> `擴充模組管理(Extension)` -> `選擇擴充模組類別(Choose the extension type)` -> `付款模組(Payments)` -> `PAYUNi 統一金流`，點選右邊 `安裝(Install)` 按鈕。
- 安裝後再點選 `編輯(Edit)` 按鈕。
![](https://raw.githubusercontent.com/payuni/sample_picture/main/opencart4/opencart4_install.png)
- 請登入[PAYUNi平台](https://www.payuni.com.tw/)檢視商店串接資訊取得商店代號、 Hash Key 及 Hash IV 並依序填入。
![](https://raw.githubusercontent.com/payuni/sample_picture/main/opencart4/opencart4_admin_page.png)
- 完成後記得儲存設定。

**注意事項**
- 請注意 Hash Key 與 Hash IV內容不可包含空白。
- 測試模式預設為`啟用`，若需要正式使用時，請將測試模式改為`停用`。