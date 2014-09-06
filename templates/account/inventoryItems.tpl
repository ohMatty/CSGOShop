{% for item_id, item in inventory %}
	<div class="inventory-item" 
		data-item-id="{{ item.id }}" 
		data-item-name="{{ item.desc.market_name }}" 
		data-item-stackable="{{ item.desc.stackable }}"
		data-item-price-preset="{{ money_format(item.desc.price_preset|default('0.00')) }}">
		<a href="#" title="{{ item.desc.market_name }}">
			<img src="http://cdn.steamcommunity.com/economy/image/{{ item.desc.icon_url_large ? item.desc.icon_url_large : item.desc.icon_url }}/100x100" alt="{{ item.desc.name }}" />
		</a>
	</div>
{% endfor %}