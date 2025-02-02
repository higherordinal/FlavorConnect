import { formatFraction, addSafeEventListener, fetchData } from '../utils/common.js';

document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scroll behavior for the "Back to Recipes" link
    const backLink = document.querySelector('.back-link');
    if (backLink) {
        backLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            setTimeout(() => {
                window.location = this.href;
            }, 300);
        });
    }

    // Add hover effect for recipe meta items
    const metaItems = document.querySelectorAll('.recipe-meta span');
    metaItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.color = 'var(--color-primary)';
        });
        item.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.color = '';
        });
    });

    // Initialize video container aspect ratio
    const videoContainer = document.querySelector('.video-container');
    if (videoContainer) {
        const iframe = videoContainer.querySelector('iframe');
        if (iframe) {
            // Set 16:9 aspect ratio
            videoContainer.style.paddingBottom = '56.25%';
        }
    }

    // Add print recipe functionality only if print button doesn't exist
    if (!document.querySelector('.print-recipe-btn')) {
        const printButton = document.createElement('button');
        printButton.className = 'print-recipe-btn';
        printButton.innerHTML = '<i class="fas fa-print"></i> Print Recipe';
        
        // Insert print button after recipe description
        const recipeDescription = document.querySelector('.recipe-description');
        if (recipeDescription) {
            recipeDescription.parentNode.insertBefore(printButton, recipeDescription.nextSibling);
        }

        // Add click event for printing
        printButton.addEventListener('click', function() {
            const printContent = document.createElement('div');
            const recipe = document.querySelector('.recipe-show');
            
            // Only clone the content we want to print
            const title = document.querySelector('.recipe-header-overlay h1').cloneNode(true);
            const description = document.querySelector('.recipe-description').cloneNode(true);
            const meta = document.querySelector('.recipe-meta').cloneNode(true);
            const ingredients = document.querySelector('.recipe-ingredients').cloneNode(true);
            const directions = document.querySelector('.recipe-directions').cloneNode(true);
            
            // Build print content
            printContent.appendChild(title);
            printContent.appendChild(description);
            printContent.appendChild(meta);
            printContent.appendChild(ingredients);
            printContent.appendChild(directions);
            
            // Create print window
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Recipe</title>
                    <link rel="stylesheet" href="${window.location.origin}/assets/css/pages/recipe-show.css">
                    <style>
                        @media print {
                            body { 
                                padding: 40px;
                                font-size: 12pt;
                                line-height: 1.5;
                                max-width: 800px;
                                margin: 0 auto;
                            }
                            h1 { 
                                color: #000;
                                text-shadow: none;
                                font-size: 24pt;
                                margin-bottom: 20px;
                                text-align: center;
                            }
                            .recipe-description {
                                margin-bottom: 20px;
                                text-align: center;
                                font-style: italic;
                            }
                            .recipe-meta {
                                display: flex !important;
                                justify-content: center;
                                gap: 20px;
                                margin-bottom: 30px;
                                border-top: 1px solid #ccc;
                                border-bottom: 1px solid #ccc;
                                padding: 15px 0;
                                text-align: center;
                            }
                            .recipe-meta span {
                                display: inline-block;
                                margin: 0 10px;
                            }
                            .recipe-meta i {
                                margin-right: 5px;
                            }
                            .recipe-ingredients,
                            .recipe-directions {
                                box-shadow: none;
                                border: none;
                                margin-bottom: 30px;
                            }
                            .recipe-ingredients h2,
                            .recipe-directions h2 {
                                font-size: 18pt;
                                margin-bottom: 15px;
                                border-bottom: 2px solid #000;
                                padding-bottom: 5px;
                            }
                            .scaling-buttons,
                            .print-recipe-btn,
                            .recipe-actions,
                            .recipe-meta {
                                display: none;
                            }
                            .ingredients-list,
                            .directions-list {
                                padding-left: 20px;
                            }
                            .ingredients-list li,
                            .directions-list li {
                                margin-bottom: 8px;
                            }
                            .directions-list li {
                                margin-bottom: 15px;
                            }
                            .step-number {
                                display: none;
                            }
                            .directions-list {
                                list-style-type: decimal;
                                padding-left: 30px;
                            }
                            .directions-list li {
                                padding-left: 10px;
                            }
                        }
                    </style>
                </head>
                <body>
                    ${printContent.innerHTML}
                </body>
                </html>
            `);
            
            // Wait for styles to load then print
            printWindow.document.addEventListener('load', function() {
                printWindow.print();
                printWindow.close();
            }, true);
        });
    }

    // Add CSS for print button and print layout
    const existingStyle = document.querySelector('#recipe-show-styles');
    if (!existingStyle) {
        const style = document.createElement('style');
        style.id = 'recipe-show-styles';
        style.textContent = `
            .print-recipe-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                margin: 1rem 0;
                padding: 0.75rem 1.5rem;
                background-color: var(--color-gray-700);
                color: var(--color-white);
                border: none;
                border-radius: var(--radius-sm);
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            .print-recipe-btn:hover {
                background-color: var(--color-gray-800);
                transform: translateY(-1px);
            }

            @media print {
                .back-link, 
                .print-recipe-btn,
                .scaling-buttons,
                .comments-section,
                footer {
                    display: none !important;
                }

                .recipe-show {
                    margin: 0;
                    padding: 0;
                }

                .recipe-header-image {
                    max-height: 300px;
                }

                .recipe-ingredients,
                .recipe-directions {
                    box-shadow: none;
                    border: none;
                    margin: 1rem 0;
                    padding: 1rem 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});
