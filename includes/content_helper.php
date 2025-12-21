<?php
/**
 * Content Helper
 * Xử lý hiển thị nội dung với code blocks và ảnh đính kèm
 */

/**
 * Render nội dung với code blocks được highlight
 * @param string $content Nội dung gốc
 * @return string HTML đã được xử lý
 */
function renderContent($content)
{
    // Escape HTML trước
    $content = htmlspecialchars($content);

    // Xử lý code blocks (```language ... ```)
    $content = preg_replace_callback(
        '/```(\w*)\n([\s\S]*?)```/m',
        function ($matches) {
            $language = $matches[1] ?: 'text';
            $code = trim($matches[2]);
            $uniqueId = 'code-' . uniqid();

            // Áp dụng syntax highlighting cơ bản
            $highlightedCode = applySyntaxHighlight($code, $language);

            // Thêm line numbers
            $lines = explode("\n", $highlightedCode);
            $lineCount = count($lines);
            $numberedCode = '';
            foreach ($lines as $i => $line) {
                $numberedCode .= '<span class="code-line">' . $line . '</span>' . ($i < $lineCount - 1 ? "\n" : '');
            }

            return '<div class="code-block" id="' . $uniqueId . '">
                <div class="code-block-header">
                    <span class="code-block-language">' . htmlspecialchars($language) . ' • ' . $lineCount . ' lines</span>
                    <button type="button" class="code-block-copy" onclick="copyCode(\'' . $uniqueId . '\')">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <pre><code class="language-' . htmlspecialchars($language) . '">' . $numberedCode . '</code></pre>
            </div>';
        },
        $content
    );

    // Xử lý inline code (`code`)
    $content = preg_replace(
        '/`([^`]+)`/',
        '<code class="inline-code">$1</code>',
        $content
    );

    // Chuyển xuống dòng thành <br>
    $content = nl2br($content);

    return $content;
}

/**
 * Áp dụng syntax highlighting cơ bản
 * @param string $code Code cần highlight
 * @param string $language Ngôn ngữ
 * @return string Code đã được highlight
 */
function applySyntaxHighlight($code, $language)
{
    // Keywords cho các ngôn ngữ phổ biến
    $keywords = [
        'php' => ['function', 'class', 'public', 'private', 'protected', 'static', 'return', 'if', 'else', 'elseif', 'foreach', 'for', 'while', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'throw', 'new', 'echo', 'print', 'require', 'include', 'require_once', 'include_once', 'use', 'namespace', 'extends', 'implements', 'interface', 'trait', 'abstract', 'final', 'const', 'true', 'false', 'null', 'array', 'string', 'int', 'float', 'bool', 'void'],
        'javascript' => ['function', 'const', 'let', 'var', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'throw', 'new', 'class', 'extends', 'import', 'export', 'default', 'async', 'await', 'true', 'false', 'null', 'undefined', 'this', 'super', 'typeof', 'instanceof'],
        'python' => ['def', 'class', 'return', 'if', 'elif', 'else', 'for', 'while', 'try', 'except', 'finally', 'raise', 'import', 'from', 'as', 'with', 'pass', 'break', 'continue', 'True', 'False', 'None', 'and', 'or', 'not', 'in', 'is', 'lambda', 'yield', 'global', 'nonlocal', 'assert', 'del'],
        'sql' => ['SELECT', 'FROM', 'WHERE', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'SET', 'DELETE', 'CREATE', 'TABLE', 'ALTER', 'DROP', 'INDEX', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER', 'ON', 'AND', 'OR', 'NOT', 'NULL', 'ORDER', 'BY', 'GROUP', 'HAVING', 'LIMIT', 'OFFSET', 'AS', 'DISTINCT', 'COUNT', 'SUM', 'AVG', 'MAX', 'MIN', 'LIKE', 'IN', 'BETWEEN', 'EXISTS', 'UNION', 'ALL', 'PRIMARY', 'KEY', 'FOREIGN', 'REFERENCES', 'CASCADE', 'DEFAULT', 'AUTO_INCREMENT'],
        'java' => ['public', 'private', 'protected', 'static', 'final', 'class', 'interface', 'extends', 'implements', 'return', 'if', 'else', 'for', 'while', 'switch', 'case', 'break', 'continue', 'try', 'catch', 'finally', 'throw', 'throws', 'new', 'import', 'package', 'void', 'int', 'long', 'double', 'float', 'boolean', 'char', 'String', 'true', 'false', 'null', 'this', 'super', 'abstract', 'synchronized'],
        'css' => ['color', 'background', 'margin', 'padding', 'border', 'width', 'height', 'display', 'position', 'top', 'left', 'right', 'bottom', 'font', 'text', 'flex', 'grid', 'align', 'justify', 'transform', 'transition', 'animation', 'opacity', 'visibility', 'overflow', 'z-index', 'box-shadow', 'border-radius'],
        'html' => ['html', 'head', 'body', 'div', 'span', 'p', 'a', 'img', 'ul', 'ol', 'li', 'table', 'tr', 'td', 'th', 'form', 'input', 'button', 'select', 'option', 'textarea', 'label', 'script', 'style', 'link', 'meta', 'title', 'header', 'footer', 'nav', 'main', 'section', 'article', 'aside']
    ];

    $lang = strtolower($language);

    // Highlight comments
    $code = preg_replace('/(\/\/.*$)/m', '<span class="comment">$1</span>', $code);
    $code = preg_replace('/(#.*$)/m', '<span class="comment">$1</span>', $code);
    $code = preg_replace('/(\/\*[\s\S]*?\*\/)/m', '<span class="comment">$1</span>', $code);

    // Highlight strings
    $code = preg_replace('/(&quot;[^&]*&quot;|\'[^\']*\'|"[^"]*")/', '<span class="string">$1</span>', $code);

    // Highlight numbers
    $code = preg_replace('/\b(\d+\.?\d*)\b/', '<span class="number">$1</span>', $code);

    // Highlight keywords
    if (isset($keywords[$lang])) {
        foreach ($keywords[$lang] as $keyword) {
            $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/';
            if ($lang === 'sql') {
                $pattern = '/\b(' . preg_quote($keyword, '/') . ')\b/i';
            }
            $code = preg_replace($pattern, '<span class="keyword">$1</span>', $code);
        }
    }

    // Highlight variables (PHP $var, JS/Python)
    $code = preg_replace('/(\$\w+)/', '<span class="variable">$1</span>', $code);

    // Highlight function calls
    $code = preg_replace('/\b(\w+)\s*\(/', '<span class="function">$1</span>(', $code);

    return $code;
}

/**
 * Render ảnh đính kèm
 * @param string $imagesJson JSON array chứa URLs ảnh
 * @return string HTML hiển thị ảnh
 */
function renderAttachedImages($imagesJson)
{
    if (empty($imagesJson)) {
        return '';
    }

    $images = json_decode($imagesJson, true);
    if (!is_array($images) || empty($images)) {
        return '';
    }

    $count = count($images);
    $html = '<div class="attached-images mt-3">';
    $html .= '<small class="text-muted d-block mb-2"><i class="bi bi-images me-1"></i>' . $count . ' ảnh đính kèm</small>';
    $html .= '<div class="image-gallery">';

    foreach ($images as $index => $url) {
        $safeUrl = htmlspecialchars($url);
        $html .= '<div class="image-gallery-item" onclick="openLightbox(\'' . $safeUrl . '\', ' . json_encode($images) . ', ' . $index . ')">';
        $html .= '<img src="' . $safeUrl . '" alt="Ảnh đính kèm ' . ($index + 1) . '" loading="lazy">';
        $html .= '<div class="image-overlay"><i class="bi bi-zoom-in"></i></div>';
        $html .= '</div>';
    }

    $html .= '</div></div>';

    return $html;
}

/**
 * Render nội dung đầy đủ (text + code + ảnh)
 * @param string $content Nội dung text
 * @param string $imagesJson JSON array ảnh (optional)
 * @return string HTML hoàn chỉnh
 */
function renderFullContent($content, $imagesJson = '') {
    $html = '<div class="rendered-content">';
    $html .= renderContent($content);
    $html .= renderAttachedImages($imagesJson);
    $html .= '</div>';
    
    return $html;
}
