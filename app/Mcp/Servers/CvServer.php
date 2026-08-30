<?php

namespace App\Mcp\Servers;

use App\Mcp\Resources\ApiCatalogResource;
use App\Mcp\Tools\CheckAtsTool;
use App\Mcp\Tools\CreateCoverLetterTool;
use App\Mcp\Tools\CreateCvTool;
use App\Mcp\Tools\CreatePublicProfileTool;
use App\Mcp\Tools\DeleteCoverLetterTool;
use App\Mcp\Tools\DeleteCvTool;
use App\Mcp\Tools\DeletePublicProfileTool;
use App\Mcp\Tools\GetCoverLetterTool;
use App\Mcp\Tools\GetCvTool;
use App\Mcp\Tools\GetPublicProfileTool;
use App\Mcp\Tools\ListCoverLetterTemplatesTool;
use App\Mcp\Tools\ListCvTemplatesTool;
use App\Mcp\Tools\ListMyCoverLettersTool;
use App\Mcp\Tools\ListMyCvsTool;
use App\Mcp\Tools\ListPublicProfileTemplatesTool;
use App\Mcp\Tools\RenderCoverLetterPdfTool;
use App\Mcp\Tools\RenderCvPdfTool;
use App\Mcp\Tools\UpdateCoverLetterTool;
use App\Mcp\Tools\UpdateCvTool;
use App\Mcp\Tools\UpdatePublicProfileTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('CV')]
#[Version('1.0.0')]
#[Instructions(<<<'MARKDOWN'
This server lets an AI agent build CVs, cover letters, and public profiles, run ATS checks, and render PDFs.

Public tools work without a token. Saved-account tools (list/get/update/delete) only appear when the client sends `Authorization: Bearer <agent-token>`.

Always send `X-Agent-Client: <platform>` (chatgpt, claude, copilot, gemini, cursor, …) so usage can be attributed.

Prefer: list templates → collect user_data → create_cv / check_ats → render_cv_pdf.
Read the `api-catalog` resource for the REST catalog if you prefer HTTP over tools.
MARKDOWN)]
class CvServer extends Server
{
    protected array $tools = [
        ListCvTemplatesTool::class,
        ListCoverLetterTemplatesTool::class,
        ListPublicProfileTemplatesTool::class,
        CheckAtsTool::class,
        CreateCvTool::class,
        RenderCvPdfTool::class,
        CreateCoverLetterTool::class,
        RenderCoverLetterPdfTool::class,
        ListMyCvsTool::class,
        GetCvTool::class,
        UpdateCvTool::class,
        DeleteCvTool::class,
        ListMyCoverLettersTool::class,
        GetCoverLetterTool::class,
        UpdateCoverLetterTool::class,
        DeleteCoverLetterTool::class,
        GetPublicProfileTool::class,
        CreatePublicProfileTool::class,
        UpdatePublicProfileTool::class,
        DeletePublicProfileTool::class,
    ];

    protected array $resources = [
        ApiCatalogResource::class,
    ];

    protected array $prompts = [];
}
