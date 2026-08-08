<style>
    #task-tree-app {
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    #task-tree-app .v-application {
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* RTL Support */
    #task-tree-app[dir="rtl"] {
        direction: rtl;
    }

    #task-tree-app[dir="rtl"] .task-row {
        direction: rtl;
    }

    #task-tree-app[dir="rtl"] .header-row {
        direction: rtl;
    }

    #task-tree-app[dir="rtl"] .task-title-wrapper {
        flex-direction: row-reverse;
    }

    #task-tree-app[dir="rtl"] .user-info {
        flex-direction: row-reverse;
    }

    #task-tree-app[dir="rtl"] .task-meta {
        direction: rtl;
    }

    #task-tree-app[dir="rtl"] .expand-button v-icon {
        transform: scaleX(-1);
    }

    /* RTL Hierarchy indentation - swap padding sides */
    #task-tree-app[dir="rtl"] .task-row.epic-row .col-expand {
        padding-right: 0px;
        padding-left: 0px;
    }

    #task-tree-app[dir="rtl"] .task-row.feature-row .col-expand {
        padding-right: 24px;
        padding-left: 0px;
    }

    #task-tree-app[dir="rtl"] .task-row.story-row .col-expand {
        padding-right: 48px;
        padding-left: 0px;
    }

    #task-tree-app[dir="rtl"] .task-row.task-row-default .col-expand {
        padding-right: 72px;
        padding-left: 0px;
    }

    .hierarchy-container {
        padding: 20px;
        background: #f8f9fa;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
    }

    /* Main Layout - NO cards, direct table-like rows */
    .task-list {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }

    .task-list-content {
        overflow-y: auto;
        overflow-x: auto;
        flex: 1;
        min-height: 0;
    }

    .task-row {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        background: white;
        position: relative;
        min-height: 60px;
        min-width: fit-content;
    }

    .task-row:hover {
        background: #f8f9fa;
    }

    .task-row:last-child {
        border-bottom: none;
    }

    /* Hierarchy indentation and borders */
    .task-row.epic-row {
        /* border-left: 4px solid #28af51;  Using .bordlft-green from custom.css */
    }

    .task-row.feature-row {
        /* border-left: 4px solid #28af51; */
        padding-left: 40px;
    }

    .task-row.story-row {
        /* border-left: 4px solid #28af51; */
        padding-left: 64px;
    }

    .task-row.task-row-default {
        /* border-left: 4px solid #28af51; */
        padding-left: 88px;
    }

    /* LTR Hierarchy indentation on col-expand */
    #task-tree-app[dir="ltr"] .task-row.epic-row .col-expand {
        padding-left: 0px;
    }

    #task-tree-app[dir="ltr"] .task-row.feature-row .col-expand {
        padding-left: 24px;
    }

    #task-tree-app[dir="ltr"] .task-row.story-row .col-expand {
        padding-left: 48px;
    }

    #task-tree-app[dir="ltr"] .task-row.task-row-default .col-expand {
        padding-left: 72px;
    }

    /* Toolbar matching original */
    .main-toolbar {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Header row */
    .header-row {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        background: #f8f9fa;
        border-bottom: 2px solid #e0e0e0;
        font-size: 12px;
        color: #6c757d;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
        min-width: fit-content;
    }

    /* Sortable header columns */
    .sortable-header {
        cursor: pointer;
        user-select: none;
        position: relative;
        transition: background-color 0.2s;
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 4px;
    }

    .sortable-header:hover {
        background: #e9ecef;
        color: #495057;
    }

    .sortable-header .sort-icon {
        opacity: 0.7;
        margin-left: auto;
    }

    .sortable-header:hover .sort-icon {
        opacity: 1;
    }

    /* Column widths to match original exactly */
    .col-expand {
        width: 32px; /* Expanded to accommodate hierarchy indentation */
        flex-shrink: 0;
    }

    .col-checkbox {
        width: 0px; /* Hidden - not used */
        flex-shrink: 0;
        display: none;
    }

    .col-actions {
        width: 40px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .col-number {
        width: 80px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
    }

    .col-title {
        flex: 1;
        min-width: 300px;
        display: flex;
        align-items: center;
    }

    .col-assigned {
        width: 150px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .col-priority {
        width: 100px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .col-updated {
        width: 120px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .col-status {
        width: 120px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .col-due {
        width: 140px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Task content styling */
    .task-number {
        font-weight: 600;
        color: #495057;
        font-size: 14px;
    }

    .task-title-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        width: 100%;
    }

    .task-title {
        font-weight: 500;
        color: #212529;
        font-size: 14px;
        line-height: 1.3;
        margin-bottom: 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .task-meta {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .created-txt {
        margin-top: 4px;
        margin-right: 10px;
        font-size: 12px;
        color: #555;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* User info */
    .user-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .user-info .text-body-2 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100px;
    }

    /* Expand button */
    .expand-button {
        width: 16px;
        height: 16px;
        min-width: 16px !important;
        margin-right: 8px;
    }

    /* Task Tree Type Icons - Using sprite system from custom.css */
    .tasktree-type-icon {
        position: relative;
        display: inline-block;
        width: 18px;
        height: 18px;
        margin-right: 4px;
        vertical-align: middle;
        flex-shrink: 0;
    }

    .tasktree-type-icon:before {
        content: '';
        background: url(<?= $this->Url->build('/img/ttsprite.png') ?>) no-repeat 0 -256px;
        width: 18px;
        height: 18px;
        display: block;
        position: absolute;
        left: 0;
        top: 0;
    }

    /* Sprite positions for each task type */
    .tasktree-type-icon.tt_bug:before {
        background-position: 0 3px;
    }

    .tasktree-type-icon.tt_change-request:before {
        background-position: 0 -23px;
    }

    .tasktree-type-icon.tt_development:before {
        background-position: 0 -46px;
    }

    .tasktree-type-icon.tt_enhancement:before {
        background-position: 0 -69px;
    }

    .tasktree-type-icon.tt_idea:before {
        background-position: 0 -92px;
    }

    .tasktree-type-icon.tt_maintenance:before {
        background-position: 0 -115px;
    }

    .tasktree-type-icon.tt_quality-assurance:before {
        background-position: 0 -138px;
    }

    .tasktree-type-icon.tt_release:before {
        background-position: 0 -165px;
    }

    .tasktree-type-icon.tt_research-n-do:before {
        background-position: 0 -186px;
    }

    .tasktree-type-icon.tt_unit-testing:before {
        background-position: 0 -211px;
    }

    .tasktree-type-icon.tt_update:before {
        background-position: 0 -233px;
    }

    .tasktree-type-icon.tt_others:before {
        background-position: 0 -258px;
    }

    .tasktree-type-icon.tt_epic:before {
        background-position: 0 -280px;
    }

    .tasktree-type-icon.tt_story:before {
        background-position: 0 -304px;
    }

    .tasktree-type-icon.tt_feature:before {
        background-position: 0 -46px;
        /* Using development icon for feature */
    }

    /* Chips matching original */
    .priority-chip {
        color: #000 !important;
        height: 24px;
        border-radius: 5px;
    }

    /* Mass action dropdown */
    .mass-action-dropdown {
        position: relative;
        display: inline-block;
    }

    .mass-action-dropdown .dropdown-toggle {
        cursor: pointer;
        padding: 4px;
        display: inline-flex;
        align-items: center;
    }

    .mass-action-dropdown .dropdown-toggle.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .mass-action-dropdown .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: none;
        min-width: 200px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        margin-top: 4px;
    }

    .mass-action-dropdown .dropdown-menu.show {
        display: block;
    }

    .mass-action-dropdown .dropdown-menu li {
        list-style: none;
    }

    .mass-action-dropdown .dropdown-menu li a {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        color: #212529;
        text-decoration: none;
        font-size: 14px;
        gap: 8px;
    }

    .mass-action-dropdown .dropdown-menu li a:hover {
        background: #f8f9fa;
    }

    .mass-action-dropdown .dropdown-menu li a i {
        font-size: 18px;
    }

    /* Action menu for each task row */
    .col-actions .dropdown {
        position: relative;
        display: inline-block;
    }

    .col-actions a {
        text-decoration: none;
    }

    .action-menu-toggle {
        cursor: pointer;
        padding: 4px;
        display: inline-flex;
        align-items: center;
        color: #6c757d;
        text-decoration: none;
    }

    .action-menu-toggle:hover {
        color: #212529;
        background: #f8f9fa;
        border-radius: 4px;
    }

    .action-menu {
        position: absolute;
        top: 100%;
        right: 0;
        z-index: 1000;
        display: none;
        min-width: 200px;
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        margin-top: 4px;
        padding: 4px 0;
    }

    .action-menu.show {
        display: block;
    }

    .action-menu li {
        list-style: none;
    }

    .action-menu li a {
        display: flex;
        align-items: center;
        padding: 8px 16px;
        color: #212529;
        text-decoration: none;
        font-size: 14px;
        gap: 8px;
        cursor: pointer;
    }

    .action-menu li a:hover {
        background: #f8f9fa;
    }

    .action-menu li a i {
        font-size: 18px;
    }

    .action-menu li.divider {
        height: 1px;
        margin: 4px 0;
        background: #e0e0e0;
    }

    .status-chip {
        color: white !important;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 500;
        height: 24px;
        white-space: nowrap;
    }

    .type-chip {
        font-size: 10px;
        height: 18px;
        color: white !important;
        font-weight: 500;
    }

    .priority-chip {
        border-radius: 5px;
        font-size: 11px;
        font-weight: 500;
        height: 24px;
        white-space: nowrap;
    }

    /* User avatar */
    .user-info {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    /* Quick task button */
    .quick-task-btn {
        border: 2px dashed #dee2e6 !important;
        color: #6c757d;
        text-transform: none;
        font-weight: normal;
        margin-bottom: 16px;
    }

    /* Loading indicator */
    .loading-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        gap: 16px;
    }

    .loading-text {
        font-size: 14px;
        color: #6c757d;
        font-weight: 500;
    }

    /* No tasks message */
    .no-tasks-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        gap: 16px;
    }

    .no-tasks-text {
        font-size: 16px;
        color: #6c757d;
        font-weight: 500;
    }

    /* RTL-specific adjustments */
    #task-tree-app[dir="rtl"] .toolbar-left,
    #task-tree-app[dir="rtl"] .toolbar-right {
        flex-direction: row-reverse;
    }

    #task-tree-app[dir="rtl"] .col-expand {
        margin-right: 0;
        margin-left: 0;
    }

    #task-tree-app[dir="rtl"] .created-txt {
        margin-right: 0px;
        margin-left: 10px;
    }

    #task-tree-app[dir="rtl"] .task-meta {
        flex-direction: row-reverse;
    }

    #task-tree-app[dir="rtl"] .type-chip {
        margin-right: 0;
        margin-left: 4px;
    }
</style>