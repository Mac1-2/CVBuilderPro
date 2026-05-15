<?php
class BlockRenderer {
    public function renderFromBlocks(array $blocks, array $globalStyles = []): array {
        $html = "<div class='cv-template-root'>\n";
        $css = ".cv-template-root { " . $this->globalStylesToCss($globalStyles) . " }\n";

        $topBlocks = array_filter($blocks, fn($b) => $b['parent_id'] === null);
        foreach ($topBlocks as $block) {
            $rendered = $this->renderBlock($block, $blocks, $globalStyles);
            $html .= $rendered['html'] . "\n";
            $css .= $rendered['css'] . "\n";
        }

        $html .= "</div>";
        return ['html' => $html, 'css' => $css];
    }

    private function renderBlock(array $block, array $allBlocks, array $globalStyles): array {
        $config = json_decode($block['config_json'] ?? '{}', true) ?: [];
        $cssOverrides = json_decode($block['css_overrides'] ?? '{}', true) ?: [];
        $cssClass = 'block-' . $block['id'] . ' block-type-' . $block['block_type'];
        $inlineStyle = $this->buildInlineStyle($config, $cssOverrides, $globalStyles);

        switch ($block['block_type']) {
            case 'header':
                $nameField = $config['nameField'] ?? '{{first_name}} {{last_name}}';
                $titleField = $config['titleField'] ?? '{{job_title}}';
                $height = $config['height'] ?? '220px';
                $bg = $config['background'] ?? '#848484';
                $textAlign = $config['textAlign'] ?? 'center';
                $html = "<div class='{$cssClass} cv-header' style='height:{$height};background:{$bg};text-align:{$textAlign};{$inlineStyle}'>";
                $html .= "<div class='cv-header-name' style='padding:20px;'>{$nameField}</div>";
                if (!empty($config['showTitle'])) $html .= "<div class='cv-header-title'>{$titleField}</div>";
                $html .= "</div>";
                $css = ".cv-header { position:relative; } .cv-header-name { font-size:" . ($config['fontSize'] ?? '36px') . "; font-weight:" . ($config['fontWeight'] ?? '700') . "; color:" . ($config['textColor'] ?? '#fff') . "; font-family:" . ($config['fontFamily'] ?? 'Inter') . "; letter-spacing:" . ($config['letterSpacing'] ?? '2px') . "; }";
                return ['html' => $html, 'css' => $css];

            case 'sidebar':
                $width = $config['width'] ?? '300px';
                $bg = $config['background'] ?? '#f5f5f5';
                $position = $config['position'] ?? 'left';
                $html = "<div class='{$cssClass} cv-sidebar' style='width:{$width};background:{$bg};float:{$position};{$inlineStyle}'>";
                $html .= "<div class='cv-sidebar-content' style='padding:" . ($config['padding'] ?? '20px') . ";'>";
                $children = array_filter($allBlocks, fn($b) => $b['parent_id'] == $block['id']);
                foreach ($children as $child) {
                    $childRendered = $this->renderBlock($child, $allBlocks, $globalStyles);
                    $html .= $childRendered['html'];
                }
                $html .= "</div></div>";
                return ['html' => $html, 'css' => ".cv-sidebar { min-height:100%; }"];

            case 'section':
                $sectionType = $config['sectionType'] ?? 'experience';
                $title = $config['title'] ?? ucfirst($sectionType);
                $titleStyle = "font-size:" . ($config['titleSize'] ?? '18px') . ";color:" . ($config['titleColor'] ?? '#4f46e5') . ";font-weight:" . ($config['titleWeight'] ?? '600') . ";";
                if (!empty($config['titleBorder'])) $titleStyle .= "border-bottom:" . ($config['titleBorder'] ?? '2px solid #4f46e5') . ";padding-bottom:4px;";
                $html = "<div class='{$cssClass} cv-section' style='{$inlineStyle}'>";
                $html .= "<h2 class='cv-section-title' style='{$titleStyle}'>{$title}</h2>";
                $html .= "<div class='cv-section-content'>{{{$sectionType}_entries}}</div>";
                $html .= "</div>";
                return ['html' => $html, 'css' => ".cv-section { margin-bottom:20px; }"];

            case 'text':
                $content = $config['content'] ?? '';
                $textAlign = $config['textAlign'] ?? 'left';
                $html = "<div class='{$cssClass} cv-text' style='text-align:{$textAlign};{$inlineStyle}'>{$content}</div>";
                return ['html' => $html, 'css' => ""];

            case 'image':
                $src = $config['src'] ?? '';
                $width = $config['width'] ?? '120px';
                $height = $config['height'] ?? '120px';
                $borderRadius = $config['borderRadius'] ?? '0';
                $position = $config['position'] ?? 'relative';
                $style = "width:{$width};height:{$height};border-radius:{$borderRadius};position:{$position};";
                if ($position === 'absolute') {
                    $style .= "top:" . ($config['top'] ?? '0') . ";left:" . ($config['left'] ?? '0') . ";";
                }
                $html = "<div class='{$cssClass} cv-image' style='{$style}{$inlineStyle}'>";
                $html .= "<img src='{$src}' alt='' style='width:100%;height:100%;object-fit:cover;border-radius:{$borderRadius};'>";
                $html .= "</div>";
                return ['html' => $html, 'css' => ""];

            case 'divider':
                $width = $config['width'] ?? '100%';
                $height = $config['height'] ?? '2px';
                $color = $config['color'] ?? '#ddd';
                $margin = $config['margin'] ?? '20px 0';
                $html = "<div class='{$cssClass} cv-divider' style='width:{$width};height:{$height};background:{$color};margin:{$margin};{$inlineStyle}'></div>";
                return ['html' => $html, 'css' => ""];

            case 'spacer':
                $height = $config['height'] ?? '20px';
                $html = "<div class='{$cssClass} cv-spacer' style='height:{$height};{$inlineStyle}'></div>";
                return ['html' => $html, 'css' => ""];

            case 'columns':
                $cols = (int)($config['columns'] ?? 2);
                $gap = $config['gap'] ?? '20px';
                $widths = $config['columnWidths'] ?? array_fill(0, $cols, (100 / $cols) . '%');
                $html = "<div class='{$cssClass} cv-columns' style='display:flex;gap:{$gap};{$inlineStyle}'>";
                for ($i = 0; $i < $cols; $i++) {
                    $w = $widths[$i] ?? 'auto';
                    $html .= "<div class='cv-column cv-column-{$i}' style='flex:1;min-width:{$w};'>";
                    $children = array_filter($allBlocks, fn($b) => $b['parent_id'] == $block['id'] && ($b['config_json'] ? (json_decode($b['config_json'], true)['columnIndex'] ?? 0) : 0) == $i);
                    foreach ($children as $child) {
                        $childRendered = $this->renderBlock($child, $allBlocks, $globalStyles);
                        $html .= $childRendered['html'];
                    }
                    $html .= "</div>";
                }
                $html .= "</div>";
                return ['html' => $html, 'css' => ".cv-columns { }"];

            case 'grid':
                $gridCols = $config['columns'] ?? 'repeat(3, 1fr)';
                $gap = $config['gap'] ?? '10px';
                $html = "<div class='{$cssClass} cv-grid' style='display:grid;grid-template-columns:{$gridCols};gap:{$gap};{$inlineStyle}'>";
                $children = array_filter($allBlocks, fn($b) => $b['parent_id'] == $block['id']);
                foreach ($children as $child) {
                    $childRendered = $this->renderBlock($child, $allBlocks, $globalStyles);
                    $html .= $childRendered['html'];
                }
                $html .= "</div>";
                return ['html' => $html, 'css' => ""];

            case 'personal-field':
                $field = $config['field'] ?? 'first_name';
                $label = $config['label'] ?? '';
                $placeholder = "{{{$field}}}";
                $html = "<div class='{$cssClass} cv-personal-field' style='{$inlineStyle}'>";
                if ($label) $html .= "<span class='cv-field-label'>{$label}: </span>";
                $html .= "<span class='cv-field-value'>{$placeholder}</span>";
                $html .= "</div>";
                return ['html' => $html, 'css' => ""];

            case 'contact-line':
                $separator = $config['separator'] ?? ' &bull; ';
                $fields = $config['fields'] ?? ['email', 'phone', 'address'];
                $parts = array_map(fn($f) => "{{{$f}}}", $fields);
                $html = "<div class='{$cssClass} cv-contact-line' style='{$inlineStyle}'>" . implode($separator, $parts) . "</div>";
                return ['html' => $html, 'css' => ""];

            case 'skill-bar':
                $style = $config['displayStyle'] ?? 'tags';
                $html = "<div class='{$cssClass} cv-skills' style='{$inlineStyle}'>";
                if ($style === 'tags') {
                    $bg = $config['tagBg'] ?? '#eef2ff';
                    $color = $config['tagColor'] ?? '#4f46e5';
                    $radius = $config['tagRadius'] ?? '4px';
                    $padding = $config['tagPadding'] ?? '4px 12px';
                    $html .= "<div class='cv-skills-tags'>{{skills_entries}}</div>";
                    $css = ".cv-skills-tags { display:flex;flex-wrap:wrap;gap:8px; } .cv-skills-tags .skill-item { background:{$bg};color:{$color};padding:{$padding};border-radius:{$radius};font-size:12px; }";
                } else {
                    $html .= "{{skills_entries}}";
                    $css = "";
                }
                $html .= "</div>";
                return ['html' => $html, 'css' => $css];

            case 'entry-loop':
                $entryType = $config['entryType'] ?? 'experience';
                $layout = $config['layout'] ?? 'stacked';
                $bullet = $config['bullet'] ?? '';
                $html = "<div class='{$cssClass} cv-entry-loop cv-layout-{$layout}' style='{$inlineStyle}'>";
                $html .= "{{{$entryType}_entries}}";
                $html .= "</div>";
                return ['html' => $html, 'css' => ".cv-entry-loop { }"];

            default:
                return ['html' => "<div class='{$cssClass}' style='{$inlineStyle}'></div>", 'css' => ''];
        }
    }

    private function buildInlineStyle(array $config, array $cssOverrides, array $globalStyles): string {
        $style = '';
        $mappings = [
            'margin' => 'margin', 'padding' => 'padding',
            'marginTop' => 'margin-top', 'marginRight' => 'margin-right',
            'marginBottom' => 'margin-bottom', 'marginLeft' => 'margin-left',
            'paddingTop' => 'padding-top', 'paddingRight' => 'padding-right',
            'paddingBottom' => 'padding-bottom', 'paddingLeft' => 'padding-left',
            'borderRadius' => 'border-radius', 'border' => 'border',
            'boxShadow' => 'box-shadow', 'opacity' => 'opacity',
            'fontSize' => 'font-size', 'fontWeight' => 'font-weight',
            'fontFamily' => 'font-family', 'lineHeight' => 'line-height',
            'letterSpacing' => 'letter-spacing', 'textTransform' => 'text-transform',
            'textAlign' => 'text-align', 'color' => 'color',
            'backgroundColor' => 'background-color', 'width' => 'width',
            'height' => 'height', 'display' => 'display',
        ];
        foreach ($mappings as $configKey => $cssProp) {
            if (!empty($config[$configKey])) {
                $style .= "{$cssProp}:{$config[$configKey]};";
            }
        }
        foreach ($cssOverrides as $prop => $value) {
            $style .= "{$prop}:{$value};";
        }
        return $style;
    }

    private function globalStylesToCss(array $globalStyles): string {
        $css = '';
        if (!empty($globalStyles['colors'])) {
            foreach ($globalStyles['colors'] as $name => $value) {
                $css .= "--color-{$name}:{$value};";
            }
        }
        if (!empty($globalStyles['fonts'])) {
            if (!empty($globalStyles['fonts']['heading'])) $css .= "--font-heading:{$globalStyles['fonts']['heading']};";
            if (!empty($globalStyles['fonts']['body'])) $css .= "--font-body:{$globalStyles['fonts']['body']};";
        }
        return $css;
    }
}
